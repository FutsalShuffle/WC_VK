<?php
/**
* @package WooCommerceVKNotifications
*/
/*
Plugin Name: WooCommerce VK Notifications
Plugin URI: https://vk.com/q1337p
Description: Уведомления о заказах в личку ВК
Version: 1.0.1
Author: Andrey Burdey
Author URI: https://vk.com/q1337p
License: GPLv3
*/

defined( 'ABSPATH' ) or die('No Access!');
require_once plugin_dir_path(__FILE__) . 'vk.php';
class VkPlugin 
{
	public $plugin;
	public $settings = array();
	public $sections = array();
	public $fields = array();
	function __construct() {
		
		$this->plugin = plugin_basename(__FILE__);
		add_action( 'woocommerce_checkout_order_processed', array($this,'vk_notification'));
		}
	public function vk_notification( $order_id ) {
			$vkid = (int) esc_attr( get_option('vk_id'));
			$vkgroupid = (int) esc_attr( get_option('vk_group_id'));
			$vkgroupkey = esc_attr( get_option('vk_group_key'));
			
			$bot = new VKBot($vkgroupid, $vkgroupkey);
			$order2 = new WC_Order( $order_id );
			$currentjson = json_decode($order2, true);
			$currentnote = "<br>Пожелания к заказу: <br>";
			$currentnote .= $currentjson["customer_note"];
			$currentid = $currentjson["id"];
			$linecut = '--------------------------------------';
			$currentdate = (string) $currentjson["date_created"]["date"];
			$currentname = $currentjson["billing"]["first_name"];
			$currentaddress = $currentjson["billing"]["address_1"];
			$currentphone = (string) $currentjson["billing"]["phone"];
			$line = "Имя: ";
			$line .= $currentname;$line .= '<br> Адрес: ';$line .= $currentaddress;$line .= '<br> Номер телефона: ';$line .= $currentphone;$line .= '<br>';$line .= $currentnote;
			$order = wc_get_order($order_id);
			$fullorder = $linecut;$fullorder .= '<br>';
			foreach ($order->get_items() as $item_key => $item ):
				$fullorder .= $item["name"];$fullorder .= ' - ';$fullorder .= $item["quantity"];$fullorder .= ' шт.<br>';
				$item_total += (int)$item["total"];
			endforeach;
			$fullorder .= 'Итог: ';$fullorder .= $item_total;$fullorder .= ' руб.';$fullorder .= '<br>';$fullorder .= $line;$fullorder .= '<br>';$fullorder .= $linecut;
			$bot->send($vkid, $fullorder, '', '', 0, 0);
		}

	public function vkOptionsGroup($input){
		return $input;
	}
	public function vkSection(){
		echo '';
	}
	public function vkSection2(){
		echo '';
	}
	public function vkSection3(){
		echo '';
	}
	public function vkExample(){
		$value = esc_attr( get_option('vk_id'));
		echo '<input type="text" name="vk_id" value="'. $value .'" placeholder="id">';
	}
	public function vkExample2(){
		$value = esc_attr( get_option('vk_group_id'));
		echo '<input type="text" name="vk_group_id" value="'. $value .'" placeholder="id">';
	}
	public function vkExample3(){
		$value = esc_attr( get_option('vk_group_key'));
		echo '<input type="text" class="vk_pollkey" name="vk_group_key" value="'. $value .'" placeholder="id">';
	}
	public function setSettings(array $settings){
		$this->settings = $settings;
		return $this;
	}
	public function setSections(array $sections){
		$this->sections = $sections;
		return $this;
	}
	public function setFields(array $fields){
		$this->fields = $fields;
		return $this;
	}
	public function setSettings2() {
		$args = array(
			array(
			'option_group'=> 'vk_options_group',
			'option_name'=> 'vk_id',
			'callback' => array($this, 'vkOptionsGroup')
			),array(
			'option_group'=> 'vk_options_group',
			'option_name'=> 'vk_group_id',
			'callback' => array($this, 'vkOptionsGroup')
			),array(
			'option_group'=> 'vk_options_group',
			'option_name'=> 'vk_group_key',
			'callback' => array($this, 'vkOptionsGroup')
			)
			
			);
		$this->setSettings($args);
	}
	public function setSections2() {
		$args2 = array(
		array(
		'id' => 'vk_admin_index',
		'title' => '',
		'callback' => array($this, 'vkSection'),
		'page' =>'vk_plugin'
		),array(
		'id' => 'vk_admin_index',
		'title' => '',
		'callback' => array($this, 'vkSection2'),
		'page' =>'vk_plugin'
		),array(
		'id' => 'vk_admin_index',
		'title' => '',
		'callback' => array($this, 'vkSection3'),
		'page' =>'vk_plugin'
		)
		);
		$this->setSections($args2);
	}
	public function setFields2() {
		$args3 = array(
		array(
		'id' => 'vk_id',
		'title' => 'ID',
		'callback' => array($this, 'vkExample'),
		'page' =>'vk_plugin',
		'section' =>'vk_admin_index',
		'args' => array(
		'label_for' => 'vk_id',
		'class' =>'vks'
		)
		),
		array(
		'id' => 'vk_group_id',
		'title' => 'ID группы',
		'callback' => array($this, 'vkExample2'),
		'page' =>'vk_plugin',
		'section' =>'vk_admin_index',
		'args' => array(
		'label_for' => 'vk_group_id',
		'class' =>'vks'
		)
		),
		array(
		'id' => 'vk_group_key',
		'title' => 'Longpoll key',
		'callback' => array($this, 'vkExample3'),
		'page' =>'vk_plugin',
		'section' =>'vk_admin_index',
		'args' => array(
		'label_for' => 'vk_group_key',
		'class' =>'vks'
		)
		)
		);
		$this->setFields($args3);
	}

	public function registerCustomFields(){
		foreach ( $this->settings as $setting) {
			register_setting($setting["option_group"], $setting["option_name"], $setting["callback"]);
			
		}
		foreach ( $this->sections as $section) {
			add_settings_section($section["id"], $section["title"], $section["callback"],$section["page"] );
		}
		foreach ( $this->fields as $field) {
			add_settings_field($field["id"], $field["title"], $field["callback"],$field["page"], $field["section"], $field["args"]);
		}
	}
	function register(){
		add_action('admin_menu', array($this, 'add_admin_pages'));
		add_filter("plugin_action_links_$this->plugin", array($this, 'settings_link'));
		add_action( 'woocommerce_checkout_order_processed', array($this,'vk_notification'));
		$this->setSettings2();
		$this->setSections2();
		$this->setFields2();
		add_action('admin_init', array($this, 'registerCustomFields'));
	}
	function settings_link($links){
		$settings_link = '<a href="options-general.php?page=vk_plugin">Settings</a>';
		array_push($links, $settings_link);
		return $links;
	
	}
	function activate(){
		
		flush_rewrite_rules();
	}
	public function add_admin_pages() {
		add_menu_page('VK Settings', 'VK','manage_options', 'vk_plugin',array($this, 'admin_index'),'', null);
	}
	public function admin_index() {
		require_once plugin_dir_path(__FILE__) . 'template/admin.php';
	}
	function deactivate(){
		
		flush_rewrite_rules();
	}
	function uninstall() {
		
	}
	
}

if( class_exists('VkPlugin')) {
	$vkplugin = new VkPlugin();
	$vkplugin->register();
	
}
//activation
register_activation_hook(__FILE__, array($vkplugin, 'activate'));
//deactivation
register_deactivation_hook(__FILE__, array($vkplugin, 'deactivate'));