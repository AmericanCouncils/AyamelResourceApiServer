<?php

namespace Ayamel\TranscodingBundle\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use AC\Component\Transcoding\Transcoder;
use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Listens for Resource transcode jobs and processes accordingly.
 *
 * For now, this processes an entire resource at a time, eventually
 * it will probably be more efficient to schedule individual transcode jobs,
 * but that can be determined after some real use.
 *
 * The format of the message processed is the following:
 *  array(
 *      'id' => $id,                            //required: the string resource id
 *      'appendFiles' => false,                 //optional: whether or not to add transcoded files into the existing files array, or replace them
 *      'presetFilter' => array(),              //optional: limit job to specific presets
 *      'mimeFilter' => array(),                //optional: limit job to specific mimes
 *      'replacePreexisting' => true            //optional: whether or not to replace a preexisting file
 *      'notifyClient' => true                  //optional: whether or not to send notifications to the client upon success/failure of job
 *  );
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class Consumer implements ConsumerInterface
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Process any applicable transcode jobs for a given resource.  Returning true
     * removes the job from the queue (what we do if there is no real work to be done)
     *
     * Returning false requeues the message to be processed again later
     *
     * @param AMQPMessage $msg 
     */
	public function execute(AMQPMessage $msg)
	{
        $body = unserialize($msg->body);
		$id = $body['id'];
        $appendFiles = isset($body['appendFiles']) ? (bool) $body['appendFiles'] : false;
        $presetFilter = isset($body['presetFilter']) ? $body['presetFilter'] : array();
        $mimeFilter = isset($body['mimeFilter']) ? $body['mimeFilter'] : array();
        $notifyClient = isset($body['notifyClient']) ? $body['notifyClient'] : false;
        
        //try the transcode, if it fails, depending on how, either remove the job from the queue
        //or requeue for later
        try {
            $this->container->get('ayamel.transcoding.manager')->transcodeResource($id, $appendFiles, $presetFilter, $mimeFilter);
        } catch (ResourceLockedException $e) {
            return false;
        } catch (NoTranscodeableFilesException $e) {
            return true;
        } catch (ResourceNotFoundException $e) {
            return true;
        } catch (ResourceDeletedException $e) {
            return true;
        } catch (NoRelevantPresetsException $e) {
            return true;
        } catch (\Exception $e) {
            if ($notifyClient) {
                //TODO:
                //for any other error that we didn't expect, notify the client
                //by publishing another rabbitMQ message to send an email
                //saying there was a problem
            }
            
            //throw $e;

            //and try handling the message again
            return true;
        }
        
        //if we got this far we've transcoded everything cleanly
        if ($notifyClient) {
            //schedule success message
        }
        
        return true;
	}
        
}
