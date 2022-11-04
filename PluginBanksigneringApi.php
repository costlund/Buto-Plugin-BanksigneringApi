<?php
class PluginBanksigneringApi{
  private $data = null;
  private $server = null;
  function __construct($mode = 'prod') {
    $this->data = new PluginWfYml(__DIR__.'/data/data.yml');
    /**
     * 
     */
    if($mode!='prod' && $mode != 'test'){
      throw new Exception(__CLASS__.' says: Param mode must have value prod or test!');
    }
    /**
     * 
     */
    $this->data->set('mode', $mode);
    wfPlugin::includeonce('server/json');
    $this->server = new PluginServerJson();
  }
  public function set_data($data){
    $data = new PluginWfArray($data);
    $this->data->set('account/apiUser', $data->get('apiUser'));
    $this->data->set('account/password', $data->get('password'));
    $this->data->set('account/companyApiGuid', $data->get('companyApiGuid'));
    return null;
  }
  public function get_data(){
    return $this->data;
  }
  private function get_url(){
    $mode = $this->data->get('mode');
    return $this->data->get("url/$mode");
  }
  public function unset_session(){
    wfUser::unsetSession('plugin/banksignering/api');
  }
  public function get_session(){
    return new PluginWfArray(wfUser::getSession()->get('plugin/banksignering/api'));
  }
  public function get_auth(){
    wfUser::setSession('plugin/banksignering/api/response/auth_data/time_end', time());
    wfUser::setSession('plugin/banksignering/api/response/auth_data/time_count', wfUser::getSession()->get('plugin/banksignering/api/response/auth_data/time_end')-wfUser::getSession()->get('plugin/banksignering/api/response/auth_data/time_start'));
    return wfUser::getSession()->get('plugin/banksignering/api/response/auth');
  }
  public function get_sign(){
    wfUser::setSession('plugin/banksignering/api/response/sign_data/time_end', time());
    wfUser::setSession('plugin/banksignering/api/response/sign_data/time_count', wfUser::getSession()->get('plugin/banksignering/api/response/sign_data/time_end')-wfUser::getSession()->get('plugin/banksignering/api/response/sign_data/time_start'));
    return wfUser::getSession()->get('plugin/banksignering/api/response/sign');
  }
  public function get_qr_image(){
    return wfUser::getSession()->get('plugin/banksignering/api/response/collectqr/apiCallResponse/qrImage');
  }
  public function get_qr_string(){
    return wfUser::getSession()->get('plugin/banksignering/api/response/collectqr/apiCallResponse/qrString');
  }
  public function continue(){
    if($this->success()){
      return false;
    }elseif(
      wfUser::getSession()->get('plugin/banksignering/api/response/collectstatus/apiCallResponse/Success') 
      && wfUser::getSession()->get('plugin/banksignering/api/response/collectstatus/apiCallResponse/StatusMessage') != 'failed'
      )
    {
      return true;
    }else{
      return false;
    }
  }
  public function success(){
    if(
      wfUser::getSession()->get('plugin/banksignering/api/response/collectstatus/apiCallResponse/Success') 
      && wfUser::getSession()->get('plugin/banksignering/api/response/collectstatus/apiCallResponse/StatusMessage') == 'complete'
      )
    {
      return true;
    }else{
      return false;
    }
  }
  public function auth($personalNumber = null){
    $this->data->set('endpoint/auth/apiUser', $this->data->get('account/apiUser'));
    $this->data->set('endpoint/auth/password', $this->data->get('account/password'));
    $this->data->set('endpoint/auth/companyApiGuid', $this->data->get('account/companyApiGuid'));
    $this->data->set('endpoint/auth/endUserIp', wfServer::getRemoteAddr());
    $this->data->set('endpoint/auth/personalNumber', $personalNumber);
    $result = $this->server->send($this->get_url().'auth', $this->data->get('endpoint/auth'), 'post');
    wfUser::setSession('plugin/banksignering/api/response/auth', $result);
    wfUser::setSession('plugin/banksignering/api/endpoint/auth', $this->data->get('endpoint/auth'));
    wfUser::setSession('plugin/banksignering/api/response/auth_data', array(
      'date_time' => date('Y-m-d H:i:s'), 
      'date' => date('Y-m-d'), 
      'time_start' => time(), 
      'time_end' => null, 
      'time_count' => 0, 
      'continue' => null,
      'success' => null));
    $this->collectqr('auth');
    return null;
  }
  public function sign($userVisibleData, $personalNumber = null){
    $this->data->set('endpoint/sign/apiUser', $this->data->get('account/apiUser'));
    $this->data->set('endpoint/sign/password', $this->data->get('account/password'));
    $this->data->set('endpoint/sign/companyApiGuid', $this->data->get('account/companyApiGuid'));
    $this->data->set('endpoint/sign/endUserIp', wfServer::getRemoteAddr());
    $this->data->set('endpoint/sign/personalNumber', $personalNumber);
    $this->data->set('endpoint/sign/userVisibleData', $userVisibleData);
    $result = $this->server->send($this->get_url().'sign', $this->data->get('endpoint/sign'), 'post');
    wfUser::setSession('plugin/banksignering/api/response/sign', $result);
    wfUser::setSession('plugin/banksignering/api/endpoint/sign', $this->data->get('endpoint/sign'));
    wfUser::setSession('plugin/banksignering/api/response/sign_data', array(
      'date_time' => date('Y-m-d H:i:s'), 
      'date' => date('Y-m-d'), 
      'time_start' => time(), 
      'time_end' => null, 
      'time_count' => 0, 
      'continue' => null,
      'success' => null));
    $this->collectqr('sign');
    return null;
  }
  public function collectqr($response = 'auth'){
    $this->data->set('endpoint/collectqr/apiUser', $this->data->get('account/apiUser'));
    $this->data->set('endpoint/collectqr/password', $this->data->get('account/password'));
    $this->data->set('endpoint/collectqr/companyApiGuid', $this->data->get('account/companyApiGuid'));
    $this->data->set('endpoint/collectqr/orderRef', wfUser::getSession()->get('plugin/banksignering/api/response/'.$response.'/apiCallResponse/Response/OrderRef'));
    $result = $this->server->send($this->get_url().'collectqr', $this->data->get('endpoint/collectqr'), 'post');
    wfUser::setSession('plugin/banksignering/api/response/collectqr', $result);
    return null;
  }
  public function collectstatus($response = 'auth'){
    /**
     * 
     */
    $this->data->set('endpoint/collectstatus/apiUser', $this->data->get('account/apiUser'));
    $this->data->set('endpoint/collectstatus/password', $this->data->get('account/password'));
    $this->data->set('endpoint/collectstatus/companyApiGuid', $this->data->get('account/companyApiGuid'));
    $this->data->set('endpoint/collectstatus/orderRef', wfUser::getSession()->get('plugin/banksignering/api/response/'.$response.'/apiCallResponse/Response/OrderRef'));
    $result = $this->server->send($this->get_url().'collectstatus', $this->data->get('endpoint/collectstatus'), 'post');
    wfUser::setSession('plugin/banksignering/api/response/collectstatus', $result);
    /**
     * auth_data or sign_data
     */
    wfUser::setSession('plugin/banksignering/api/response/'.$response.'_data/time_end', time());
    wfUser::setSession('plugin/banksignering/api/response/'.$response.'_data/continue', $this->continue());
    wfUser::setSession('plugin/banksignering/api/response/'.$response.'_data/success', $this->success());
    /**
     * 
     */
    return null;
  }
}
