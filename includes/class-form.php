<?php
/**
 * Display the form
 *
 * @since 1.0.0
 */

namespace Damax_Transfers;

use NSukonny\NFramework\Singleton;

defined( 'ABSPATH' ) || exit;

class Form {

	use Singleton;

	public $settings;

	/**
	 * Init post types
	 *
	 * @since 1.0.0
	 */
	public function init() {

		add_shortcode( 'damax_transfers', array( $this, 'display_form' ) );

	}

	/**
	 * Render the form
	 *
	 * @since 1.0.0
	 */
	public function display_form() {

		if ( is_admin() ) {
			return;
		}

		if ( $this->send_form_data() ) {
			global $post;
			wp_redirect( get_permalink( $post->ID ) . '?transfers_action=thankyou' );
		}

		ob_start();
		$args = array();

		$template = 'search';

		if ( isset( $_REQUEST['transfer_address_from'] ) || isset($_GET['transport']) && ! isset( $_GET['transfers_action'] ) ) {
			$template = 'form';
		}

		if ( $overridden_template = locate_template( 'damax-transfers/' . $template . '.php' ) ) {
			load_template( $overridden_template, true, $args );

			return ob_get_clean();
		}

		load_template( PATH . '/templates/' . $template . '.php', true, $args );

		return ob_get_clean();
	}

	/**
	 * Check if we have data for send, did it to email
	 */
	private function send_form_data() {

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'transfers_save_form' ) ) {
			return false;
		}

		$mail_body = '<b>Откуда:</b> ' . $_REQUEST['transfer_address_from'];
		$mail_body .= '<br><b>Куда:</b> ' . $_REQUEST['transfer_address_to'];
		$mail_body .= '<br><a href="https://www.google.com/maps/dir/?api=1&travelmode=driving&layer=traffic&origin=' . $_REQUEST['transfer_latlng_from'] . '&destination=' . $_REQUEST['transfer_latlng_to'] . '">Показать маршрут на карте</a>';

		if ( isset( $_REQUEST['transport'] ) && 0 != count( $_REQUEST['transport'] ) ) {
			$mail_body .= '<br><b>Транспорт:</b> ';

			foreach ( $_REQUEST['transport'] as $key => $transport ) {
				if ( 0 < $key ) {
					$mail_body .= ', ';
				}
				$mail_body .= $transport;
			}
		}

		$mail_body .= '<br><b>Дата трансфера:</b> ' . $_REQUEST['transfer']['date'] . ' ' . $_REQUEST['transfer']['time'];
		$mail_body .= '<br><b>Номер рейса:</b> ' . $_REQUEST['transfer']['number'];

		if ( isset( $_REQUEST['transfer_back']['active'] ) && 1 == $_REQUEST['transfer_back']['active'] ) {
			$mail_body .= '<br><b>Дата обратного маршрута:</b> ' . $_REQUEST['transfer_back']['date'] . ' ' . $_REQUEST['transfer_back']['time'];
			$mail_body .= '<br><b>Номер рейса:</b> ' . $_REQUEST['transfer_back']['number'];
		}

		$mail_body .= '<br><b>Взрослые:</b> ' . $_REQUEST['adults'];

		if ( isset( $_REQUEST['children_seats'] ) && 0 < $_REQUEST['children_seats'] ) {
			$mail_body .= '<br><b>Детские кресла:</b> ' . $_REQUEST['children_seats'];
			if ( 0 < $_REQUEST['seat_baby'] ) {
				$mail_body .= ' (Автолюлька - ' . $_REQUEST['seat_baby'] . ')';
			}
			if ( 0 < $_REQUEST['car_seat_baby'] ) {
				$mail_body .= ' (Актокресло - ' . $_REQUEST['car_seat_baby'] . ')';
			}
			if ( 0 < $_REQUEST['cat_seat_booster'] ) {
				$mail_body .= ' (Бустер - ' . $_REQUEST['cat_seat_booster'] . ')';
			}
		}

		$mail_body .= '<br><b>Имя на табличке:</b> ' . $_REQUEST['name_on_the_table'];
		$mail_body .= '<br><b>Комментарий:</b> ' . $_REQUEST['comment'];
		$mail_body .= '<br><b>Email:</b> ' . $_REQUEST['email'];
		$mail_body .= '<br><b>Телефон:</b> ' . $_REQUEST['phone'];

		$subject = 'Новая заявка на трансфер с сайта DamaxTravel';
		$headers = array(
			'From: DamaxTravel <notifications@' . $_SERVER['HTTP_HOST'] . '>',
			'content-type: text/html',
		);

		return wp_mail( 'agpdamax@gmail.com', $subject, $mail_body, $headers );
	}

}