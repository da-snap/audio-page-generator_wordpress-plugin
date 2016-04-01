<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class AudioGenerator_Overview {

	public function __construct( ) {
	}
	public function add_help_tab ( $screen = '' ) {
		$screen = ( $screen == '' ) ? get_current_screen() : $screen;
		$screen->add_help_tab( array(
			'id' => 'list_offer_accept_decline',
			'title' => __( 'Überblick', 'audiogenerator' ),
			'content' => AudiGenerator_Overview::faq_content( 'offer_accept_decline' )
		) );
	}

	public function screen_option () {
		$option = 'per_page';
		$args = array(
			'label' => __( 'Anzahl der Seiten', 'audiogenerator' ),
			'default' => 10,
			'option' => 'audiogenerator_overview_per_page'
		);

		add_screen_option( $option, $args );
	}

	public function faq_content( $name ) {
		$content = array();
		$content['offer_accept_decline'] = '
			Um Terminanfragen zu bearbeiten, klicken Sie links in der Navigation auf Offene
			Terminanfragen. In dieser Tabelle werden alle Terminanfragen aufgelistet, die noch nicht
			zu- oder abgesagt wurden. Grau geschriebene Anfragen wurden noch nicht best&auml;tigt.
			Zugesagte Termine finden Sie unter Buchungen Verwalten.<br />
			Suchen Sie in der Tabelle den entsprechenden Eintrag und klicken Sie auf das
			Bleistiftsymbol. Auf der folgenden Seite k&ouml;nnen Sie entweder einen der Termine
			(Wunsch- oder Ausweichtermin) zusagen (gr&uuml;nes Symbol mit Haken), oder beide Termine
			absagen (rotes Symbol mit X).<br />
			Nun k&ouml;nnen Sie die entsprechende E-Mail an den Terminanforderer verfassen und die
			Zu-/Absage im System buchen. Wenn Sie keine E-Mail Nachricht an den Anforderer senden
			wollen, aktivieren Sie die Checkbox unten im Formular.<br />
			<b>Erst nachdem Sie auf Zusage senden oder Absage senden geklickt haben, wird die E-Mail
			versendet und der Vorgang im System gebucht!</b>
		';
		return '<p>' . $content[$name] . '</p>';
	}


	public function index () {


		$overview_title = __( 'Übersicht', 'audiogenerator' );
	?>

	<div id="admincontent" class="wrap">
		<h1><?php echo $overview_title; ?></h1>

		<?php
		$tours_overview = __( 'Führungsübersicht', 'audiogenerator' );
		$tours_link = add_query_arg(
			array( 'tab' => esc_attr( 'tours' ) ),
			menu_page_url( 'audiogenerator-manage-page', false )
		);
		?>
		<h1 class="audiogenerator-action-menu">
			<a class="page-title-action" href="<?php echo $tours_link; ?>"><?php echo $tours_overview; ?></a>
		</h1>
		<?php

		switch ( $_REQUEST['tab'] ) {
		case 'tours':
			echo "test";
			break;
		}

		?>
		</div>
		<?php
	}

}
