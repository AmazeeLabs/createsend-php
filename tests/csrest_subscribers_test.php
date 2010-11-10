<?php

require_once 'simpletest/autorun.php';
require_once '../class/transport.php';
require_once '../class/serialisation.php';
require_once '../class/log.php';
require_once '../csrest_subscribers.php';

@Mock::generate('CS_REST_Log');
@Mock::generate('CS_REST_NativeJsonSerialiser');
@Mock::generate('CS_REST_CurlTransport');

class CS_REST_TestSubscribers extends CS_REST_TestBase {
    var $list_id = 'not a real list id';
    var $list_base_route;

    function set_up_inner() {
        $this->list_base_route = $this->base_route.'subscribers/'.$this->list_id;
        $this->wrapper = &new CS_REST_Subscribers($this->list_id, $this->api_key, $this->protocol, $this->log_level,
        $this->api_host, $this->mock_log, $this->mock_serialiser, $this->mock_transport);
    }

    function testadd() {
        $raw_result = '';

        $call_options = $this->get_call_options($this->list_base_route.'.json', 'POST');

        $subscriber = array (
            'Email' => 'test@test.com',
            'Name' => 'Widget Man!',
            'CustomFields' => array(array(1,2), array(3,4))
        );

        $this->general_test_with_argument('add', $subscriber, $call_options,
        $raw_result, $raw_result, 'subscriber was serialised to this');
    }

    function testimport() {
        $raw_result = 'the import result';
        $response_code = 200;
        $resubscribe = true;

        $call_options = $this->get_call_options($this->list_base_route.'/import.json', 'POST');

        $subscribers = array(
            array (
    	            'Email' => 'test@test.com',
    	            'Name' => 'Widget Man!',
    	            'CustomFields' => array(array(1,2), array(3,4))
            ),
            array (
                    'Email' => 'test@test.com',
                    'Name' => 'Widget Man!',
                    'CustomFields' => array(array(1,2), array(3,4))
            )
        );

        $data = array(
                'Resubscribe' => $resubscribe,
                'Subscribers' => $subscribers 
        );

        $expected_result = array (
            'code' => $response_code, 
            'response' => $raw_result
        );

        $call_options['data'] = 'subscribers were serialised to this';
        $this->setup_transport_and_serialisation($expected_result, $call_options,
            $raw_result, $raw_result, 'subscribers were serialised to this', 
            $data, $response_code);

        $result = $this->wrapper->import($subscribers, $resubscribe);

        $expected_result['response'] = $raw_result;
        $this->assertIdentical($expected_result, $result);
    }

    function testget() {
        $raw_result = 'subscriber details';
        $deserialised = array(1,2,34,5);
        $response_code = 200;
        $email = 'test@test.com';

        $call_options = $this->get_call_options(
            $this->list_base_route.'.json?email='.urlencode($email), 'GET');

        $expected_result = array (
            'code' => $response_code, 
            'response' => $raw_result
        );

        $this->setup_transport_and_serialisation($expected_result, $call_options,
            $deserialised, $raw_result, NULL, NULL, $response_code);

        $result = $this->wrapper->get($email);

        $expected_result['response'] = $deserialised;
        $this->assertIdentical($expected_result, $result);
    }

    function testget_history() {
        $raw_result = 'subscriber history';
        $deserialised = array(1,2,34,5);
        $response_code = 200;
        $email = 'test@test.com';

        $call_options = $this->get_call_options(
            $this->list_base_route.'/history.json?email='.urlencode($email), 'GET');

        $expected_result = array (
            'code' => $response_code, 
            'response' => $raw_result
        );

        $this->setup_transport_and_serialisation($expected_result, $call_options,
        $deserialised, $raw_result, NULL, NULL, $response_code);

        $result = $this->wrapper->get_history($email);

        $expected_result['response'] = $deserialised;
        $this->assertIdentical($expected_result, $result);
    }

    function testunsubscribe() {
        $raw_result = '';
        $response_code = 200;
        $email = 'test@test.com';

        $call_options = $this->get_call_options($this->list_base_route.'/unsubscribe.json', 'POST');
         
        $subscriber = array('EmailAddress' => $email);

        $expected_result = array (
            'code' => $response_code, 
            'response' => $raw_result
        );

        $call_options['data'] = 'subscriber was serialised to this';
        $this->setup_transport_and_serialisation($expected_result, $call_options,
        $raw_result, $raw_result,
            'subscriber was serialised to this', $subscriber, $response_code);

        $result = $this->wrapper->unsubscribe($email);

        $expected_result['response'] = $raw_result;
        $this->assertIdentical($expected_result, $result);
    }
}