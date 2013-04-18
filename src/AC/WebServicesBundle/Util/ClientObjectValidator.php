<?php
namespace AC\WebServicesBundle\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\SerializerInterface;
use Metadata\MetadataFactoryInterface;

//TODO: Take into account JMS Exclusion Policies / Groups
//TODO: Take into account JMS getter/setter annotation

/**
 * A class with convenience methods for decoding and validating incoming API data for create and update actions.
 *
 * @author Evan Villemez
 */
class ClientObjectValidator
{
    private $factory;

    private $serializer;

    private $typeParser;

    private $graph = array();

    /**
     * Constructor needs dependency JMS related objects.
     *
     * @param MetadataFactoryInterface $factory
     * @param Serializer               $serializer
     * @param JmsDataTypeParser        $typeParser
     */
    public function __construct(MetadataFactoryInterface $factory, SerializerInterface $serializer, JmsDataTypeParser $typeParser)
    {
        $this->factory = $factory;
        $this->serializer = $serializer;
        $this->typeParser = $typeParser;
    }

    /**
     * Deserialize a new object from a client request.
     *
     * @param string Fully qualified name of class to deserialize.
     * @param Request Request object
     * @return mixed
     */
    public function createObjectFromRequest($className, Request $request)
    {
        $this->graph($className);
        $this->validateObjectData($this->normalizeRequestData($request), $className);

        return $this->serializer->deserialize($this->getJsonFromClient($request), $className, 'json');
    }

    /**
     * Modify an object based on data in the incoming request.
     *
     * @param string Fully qualified name of class to deserialize.
     * @param Request Request object
     * @param mixed The original object to modify
     * @return mixed
     */
    public function modifyObjectFromRequest($className, Request $request, $originalObject)
    {
        if ($className !== get_class($originalObject)) {
            throw new \LogicException(sprintf("Cannot compare [%s] to [%s] in order to modify.", $className, get_class($originalObject)));
        }

        $this->graph($className);

        $normalizedData = $this->normalizeRequestData($request);

        $this->validateObjectData($normalizedData, $className, $originalObject);

        return $originalObject;
    }

    protected function graph($className)
    {
        if (!isset($this->graph[$className])) {
            try {
                if (!$meta = $this->factory->getMetadataForClass($className)) {
                    throw new \RuntimeException(sprintf("Could not validate class [%s] - no JMS metadata found."));
                }
            } catch (\ReflectionException $e) {
                return;
            }

            //check each property for the class
            foreach ($meta->propertyMetadata as $property) {
                $name = isset($property->serializedName) ? $property->serializedName : $property->name;
                
                //HACK: to support JMS SerializerBunder PRE 1.0 split into separate library
                $type = is_string($property->type) ? $property->type : $property->type['name'];

                //this property could be some type of array of nested classes
                if (0 === strpos($type, "array<")) {
                    $nested = $this->typeParser->getNestedTypeInArray($type);
                    $this->graph[sprintf("%s.%s", $className, $name)] = array(
                        'class' => $nested['value'],
                        'array' => true
                    );

                    //recurse and graph this nested class
                    $this->graph($nested['value']);
                }

                //or it could be a reglar class name
                elseif (!$this->typeParser->isPrimitive($type)) {
                    $this->graph[sprintf("%s.%s", $className, $name)] = array(
                        'class' => $type,
                        'array' => false
                    );

                    //graph the nested object
                    $this->graph($type);
                }
            }
        }
    }

    /**
     * For now we only handle post fields or JSON request bodies
     *
     * @param  Request $request
     * @return array
     */
    protected function normalizeRequestData(Request $request)
    {
        $json = $this->getJsonFromClient($request);

        return json_decode($json, true);
    }

    /**
     * Validates an array of data based on the class.
     *
     * @return boolean
     * @throws HttpException
     */
    protected function validateObjectData($clientData, $className, $objectToModify = null)
    {
        try {
            if (!$jmsMetadata = $this->factory->getMetadataForClass($className)) {
                return;
            }
        } catch (\ReflectionException $e) {
            return;
        }
        
        if (!$clientData) {
            return;
        }

        $invalidFields = array();
        foreach ($jmsMetadata->propertyMetadata as $property) {
            $name = isset($property->serializedName) ? $property->serializedName : $property->name;
            
            //check if not exists
            if (!array_key_exists($name, $clientData)) {
                continue;
            }
            
            //read only check
            if ($property->readOnly && isset($clientData[$name])) {
                $invalidFields[] = $name;
                continue;
            }

            //check for object
            $nestedObjectPropertyName = $jmsMetadata->name.".".$name;

            if (isset($this->graph[$nestedObjectPropertyName])) {

                //the property could be an array of objects
                if ($this->graph[$nestedObjectPropertyName]['array']) {
                    $getter = 'get'.ucfirst($name);
                    $setter = 'set'.ucfirst($name);
                    $newArray = array();
                    $previous = array();

                    //check for previous array of objects
                    if ($objectToModify && method_exists($objectToModify, $getter) && $original = $objectToModify->$getter()) {
                        $previous = $original;
                    }

                    //validate data
                    $i = 0;
                    foreach ($clientData[$name] as $item) {
                        $prevObj = isset($previous[$i]) ? $previous[$i] : null;
                        $newArray[] = $this->validateObjectData($item, $this->graph[$nestedObjectPropertyName]['class'], $prevObj);
                        $i++;
                    }

                    //set new array if applicable
                    if ($objectToModify) {
                        if (method_exists($objectToModify, $setter)) {
                            $objectToModify->$setter($newArray);
                        } else {
                            $invalidFields[] = $name;
                        }
                    }

                } else {
                    $getter = 'get'.ucfirst($name);
                    $setter = 'set'.ucfirst($name);
                    $prevObj = null;

                    if ($objectToModify && method_exists($objectToModify, $getter)) {
                        $prevObj = $objectToModify->$getter();
                    }

                    $newObj = $this->validateObjectData($clientData[$name], $this->graph[$nestedObjectPropertyName]['class'], $prevObj);

                    if ($objectToModify) {
                        if (method_exists($objectToModify, $setter)) {
                            $objectToModify->$setter($newObj);
                        } else {
                            $invalidFields[] = $name;
                        }
                    }
                }
            } elseif ($objectToModify) {
                //call setter for property immediately
                $setter = 'set'.ucfirst($name);
                if (method_exists($objectToModify, $setter)) {
                    $objectToModify->$setter($clientData[$name]);
                } else {
                    $invalidFields[] = $name;
                }
            }

        }

        //now check for data submitted by client, but not present in jms metadata
        foreach ($clientData as $key => $val) {
            $found = false;
            foreach ($jmsMetadata->propertyMetadata as $property) {
                $name = isset($property->serializedName) ? $property->serializedName : $property->name;
                if ($name === $key) {
                    $found = true;
                    break;
                }
            }
        
            if (!$found) {
                $invalidFields[] = $name;
            }
        }

        if (!empty($invalidFields)) {
            throw new HttpException(400, sprintf("The following fields cannot be set by the client: %s", implode(", ", $invalidFields)));
        }

        //return the modified object if present, otherwise return new one
        return ($objectToModify) ? $objectToModify : $this->serializer->deserialize(json_encode($clientData), $className, 'json');
    }

    /**
     * Normalize incoming content into a JSON string for decoding.
     *
     * @param  Request $request
     * @return string
     */
    protected function getJsonFromClient(Request $request)
    {
        if (false !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
            return $request->getContent();
        }

        //perhaps generic post body
        parse_str($request->getContent(), $postBody);
        if ($json = json_encode($postBody)) {
            return $json;
        }

        throw new HttpException(400, "Could not reliably decode data.");
    }
    
}
