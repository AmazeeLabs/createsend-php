<?php

require_once 'simpletest/autorun.php';
require_once '../class/transport.php';
require_once '../class/serialisation.php';
require_once '../class/log.php';
require_once '../csrest_campaigns.php';

@Mock::generate('CS_REST_Log');
@Mock::generate('CS_REST_NativeJsonSerialiser');
@Mock::generate('CS_REST_CurlTransport');

class CS_REST_TestCampaigns extends CS_REST_TestBase {
    var $campaign_id = 'not a real campaign id';
    var $campaign_base_route;

    function set_up_inner() {
        $this->campaign_base_route = $this->base_route.'campaigns/'.$this->campaign_id.'/';
        $this->wrapper = &new CS_REST_Campaigns($this->campaign_id, $this->api_key, $this->protocol, $this->log_level,
        $this->api_host, $this->mock_log, $this->mock_serialiser, $this->mock_transport);
    }

    function testcreate() {
        $raw_result = 'the new campaign id';
        $client_id = 'not a real client id';
        $response_code = 200;

        $call_options = $this->get_call_options(
            $this->base_route.'campaigns/'.$client_id.'.json', 'POST');

        $campaign_data = array (
            'Name' => 'ABC Widgets',
            'Subject' => 'Widget Man!',
            'ListIDs' => array(1,2,3),
            'SegmentIDs' => array(4,5,6)
        );

        $expected_result = array (
            'code' => $response_code, 
            'response' => 'the new campaign id'
        );

        $call_options['data'] = 'campaign data was serialised to this';
        $this->setup_transport_and_serialisation($expected_result, $call_options,
            $raw_result, $raw_result, 'campaign data was serialised to this', 
            $campaign_data, $response_code);

        $result = $this->wrapper->create($client_id, $campaign_data);

        $expected_result['response'] = $raw_result;
        $this->assertIdentical($expected_result, $result);
    }

    function testsend() {
        $raw_result = '';

        $call_options = $this->get_call_options(
            $this->campaign_base_route.'send.json', 'POST');

        $schedule = array (
            'CompanyName' => 'ABC Widgets',
            'ContactName' => 'Widget Man!',
            'EmailAddress' => 'widgets@abc.net.au'
        );

        $this->general_test_with_argument('send', $schedule, $call_options,
            $raw_result, $raw_result, 'scheduling was serialised to this');
    }

    function testdelete() {
        $raw_result = '';

        $call_options = $this->get_call_options(
            trim($this->campaign_base_route, '/').'.json', 'DELETE');

        $this->general_test('delete', $call_options, $raw_result, $raw_result);
    }

    function testget_bounces() {
        $raw_result = 'some bounces';
        $deserialised = array('Bounce 1', 'Bounce 2');
        $call_options = $this->get_call_options($this->campaign_base_route.'bounces.json');

        $this->general_test('get_bounces', $call_options, $raw_result, $deserialised);
    }

    function testget_lists_and_segments() {
        $raw_result = 'some lists';
        $deserialised = array('List 1', 'List 2');
        $call_options = $this->get_call_options(
            $this->campaign_base_route.'listsandsegments.json');

        $this->general_test('get_lists_and_segments', $call_options, $raw_result, $deserialised);
    }

    function testget_summary() {
        $raw_result = 'campaign summary';
        $deserialised = array(1,2,3,4,5);
        $call_options = $this->get_call_options(
            $this->campaign_base_route.'summary.json');

        $this->general_test('get_summary', $call_options, $raw_result, $deserialised);
    }

    function testget_opens() {
        $raw_result = 'some opens';
        $since = '2020';
        $response_code = 200;
        $deserialised = array('Open 1', 'Open 2');
        $call_options = $this->get_call_options(
            $this->campaign_base_route.'opens.json?date='.$since);

        $expected_result = array (
            'code' => $response_code, 
            'response' => $raw_result
        );

        $this->setup_transport_and_serialisation($expected_result, $call_options,
            $deserialised, $raw_result, NULL, NULL, $response_code);

        $result = $this->wrapper->get_opens($since);

        $expected_result['response'] = $deserialised;
        $this->assertIdentical($expected_result, $result);
    }

    function testget_clicks() {
        $raw_result = 'some clicks';
        $since = '2020';
        $response_code = 200;
        $deserialised = array('Click 1', 'Click 2');
        $call_options = $this->get_call_options(
            $this->campaign_base_route.'clicks.json?date='.$since);

        $expected_result = array (
            'code' => $response_code, 
            'response' => $raw_result
        );

        $this->setup_transport_and_serialisation($expected_result, $call_options,
            $deserialised, $raw_result, NULL, NULL, $response_code);

        $result = $this->wrapper->get_clicks($since);

        $expected_result['response'] = $deserialised;
        $this->assertIdentical($expected_result, $result);
    }

    function testget_unsubscribes() {
        $raw_result = 'some unsubscribed';
        $since = '2020';
        $response_code = 200;
        $deserialised = array('Unsubscribe 1', 'Unsubscribe 2');
        $call_options = $this->get_call_options(
            $this->campaign_base_route.'unsubscribes.json?date='.$since);

        $expected_result = array (
            'code' => $response_code, 
            'response' => $raw_result
        );

        $this->setup_transport_and_serialisation($expected_result, $call_options,
            $deserialised, $raw_result, NULL, NULL, $response_code);

        $result = $this->wrapper->get_unsubscribes($since);

        $expected_result['response'] = $deserialised;
        $this->assertIdentical($expected_result, $result);
    }

}