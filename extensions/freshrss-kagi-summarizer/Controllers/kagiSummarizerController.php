<?php

class FreshExtension_kagiSummarizer_Controller extends Minz_ActionController {
  public function summarizeAction() {
    $this->view->_layout(false);

    $kagi_token = FreshRSS_Context::$user_conf->kagi_token;

    if ($kagi_token === null || trim($kagi_token) ==='') {
      echo json_encode(array(
        'response' => array(
          'output_text' => _t('ext.kagiSummarizer.ui.no_token_configured'),
          'error' => 'configuration'),
        'status' => 200));
      return;
    }

    $entry_id = Minz_Request::param('id');
    $entry_dao = FreshRSS_Factory::createEntryDao();
    $entry = $entry_dao->searchById($entry_id);

    if ($entry === null) {
      echo json_encode(array('status' => 404));
      return;
    }

    $entry_link = urlencode($entry->link());
    $url = 'https://kagi.com/mother/summary_labs?summary_type=summary&url=' . $entry_link;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json; charset=UTF-8',
      'Authorization: ' . $kagi_token
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);

    $response = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $response_body = substr($response, $header_size);

    echo json_encode(array(
      'response' => json_decode($response_body),
      'status' => curl_getinfo($curl, CURLINFO_HTTP_CODE)
    ));
  }
}
