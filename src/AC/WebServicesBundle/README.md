# ACWebServicesBundle #

This bundle provides generic api workflow tools for developing RESTful apis.  Provided are event listeners for handling API routes, which facilitate writing output-format-agnostic controllers, content negotiation and error handling.

*Warning:*  This bundle may be removed in the future, and replaced with `FOSRestBundle`.  This may or may not be done, depending on where development with bundle that is heading.

## Usage ##

To activate the event listeners that handle API requests, provide the `ac.webservices.api_paths` config to specify an array of API routes you want to be handled.  If it matches, event listeners will be added to invoke content format negotiation, error handling, and view handling, which will allow you to return raw data structures and objects that will automatically be encoded into the requested format.

From your controllers you can return raw data structures, which may include objects configured for serialized via the `JMSSerializerBundle`.  Information returned
from a controller will automatically be serialized into the requested data transfer format by the `serializer` service.

For example:

0. Request to `http://example.com/api/foo?_format=json`
1. Routed to controller `MyBundle\Controller\FooController::someAction`
2. Which looks like this:

        <?php

        namespace MyBundle\Controller;
        use Symfony\Bundle\FrameworkBundle\Controller\Controller;

        class FooController extends Controller
        {
            public function someAction()
            {
                return array(
                    'foo' => 'bar',
                    'baz' => 23
                );
            }
        }

3. Will return this result:

        {
            "foo": "bar",
            "baz": 23
        }
        
> Note that changing the `_format` parameter to `xml` or `yml` will return the data structure in those formats as well.  Custom serialization formats
> can also be supported via the `JMSSerializerBundle`.

> Note: If the `_format` parameter is absent, a default format wil be returned, which is usually `json`.  Also, the response format can be configured by setting
> the appropriate request `accept` headers.

### Configuration ###

This is an brief description of all configuration options provided by the bundle, more detailed descriptions are given below.

* `ac.webservices.api_paths` - an array of regex expressions to match routes which should be considered "api routes"
* `ac.webservices.allow_code_suppression` - boolean for whether or not to allow clients to suppress the http response codes, and always return `200` responses
* `ac.webservices.include_response_data` - boolean for whether or not to include the response code and message in the response data structure
* `ac.webservices.exception_map` - a map of exception classes, and the http code and message that should be returned if they are encountered, if not specified, all exceptions return `500`
* `ac.webservices.default_response_format` - the default response format, if not specified `json` is assumed, but you can change this
* `ac.webservices.include_dev_exceptions` - boolean for whether or not to include detailed exception information in API responses if in dev mode

### Response data & code suppression ###

By default, API response will also include a `response` property that includes the HTTP response code and message.  This
information is also included in the actual response, but is made availabe in the response body as a matter of convenience
for API consumers.

Also, in some cases, some clients do not properly respect the actual HTTP spec.  If dealing with such a client, the bundle
allows you to make API requests that always return a `200` response code.  If this happens, the actual HTTP code and message
will still be set properly in the response body.

The example response above, if `ac.webservices.include_response_data` is `true`, would look like this:

    {
        "response": {
            "code": 200,
            "message": "OK"
        },
        "foo": "bar",
        "baz": 23
    }

### Exceptions ###

On API routes that throw exceptions, they are caught and serialized with the response data described above.  Note that
if code suppression is turned on, the actual response code will always be `200`, and the real response code must be
retrieved in the returned object.

If an HTTP exception is thrown from the controllers, the messages and codes are preserved.  If another exception is thrown, however,
the bundle will convert it into an `HttpException` with a `500` response code and default *"Internal Server Error"* message.

This behavior is also configurable - you can specify a map of other exception classes, and the http code and message that should
be returned instead.

Exceptions return the response data structure described above, for example:

    {
        "response": {
            "code": 500,
            "message": "Internal Server Error"
        }
    }

Example configuration to map specific exceptions to their codes/messages:

    //app/config/config.yml
    ac.webservices.exception_map:
        MyBundle\Custom\Exception: [405, "This is bad..."],
        
        # if you don't specify a message, the default text for the code will be used
        AnotherBundle\Custom\Exception: [400]

### Events ###

When handling api requests, the bundle fires a few extra events for all API requests.  These are useful hooks for triggering other 
functionality, such as logging, that should apply to all API services routes.  The events fired include:

* `webservice.request` - When an API request is initiated
* `webservice.exception` - If an error is encountered during an API route
* `webservice.response` - The final response from the API
* `webservice.terminate` - After the API response has been sent

You can register a listener service for any of these events with the `ac.webservice.listener` container tag, or register
subscribers to multiple events via the `ac.webservice.subscriber` tag.

### Services ###

* `ac.webservices.object_validator` - This service will use the JMS serializer and its metadata to create, or modify pre-existing
objects from a client's incoming request.

    Example:
        
        // ... get previous object

        $this->container->get('ac.webservices.object_validator')->modifyObjectFromRequest($this->getRequest(), 'MyBundle\Namespaced\Class', $previousObject);
        
        // ... $previousObject now contains the modifications from the incoming data
