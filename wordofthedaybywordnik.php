<?php
/**
 * Plugin Name: Wordnik's  word of the day
 * Plugin URI: http://labs.wordnik.com
 * Description: Display the word of the day from Wordnik
 * Version: 0.1
 * Author: Wordnik
 * Author URI: http://wordnik.com
 * License: GNU General Public License v2
 */

class Word_of_the_day_Reverb_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'word_of_the_day_reverb_widget', // Base ID
			'Word of the day', // Name
			array( 'description' => __( 'Word of the day from Wordnik' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		if(is_single()) {
			$current_post_permalink = get_permalink();
			echo $before_widget;
			if ( ! empty( $title ) )
				echo $before_title . $title . $after_title;

			echo '<link rel="stylesheet" href="http://labs.wordnik.com.s3-website-us-west-1.amazonaws.com/word.css" type="text/css" media="all" />';
			$option_name = 'wordofdaydate' ;
			$new_value = date("d");
			if ( get_option( $option_name ) != $new_value ) {
				//make remote get api call
				$api_key = 'a37e73a4bd8433196700600b3bc02651387e831fa6001426b';
		    $remote_url = 'http://api.wordnik.com/v4/words.json/wordOfTheDay?api_key='.$api_key;	    
		    $response = wp_remote_get($remote_url, array(
		      'timeout'       => 45,
		      'redirection'   => 5,
		      'httpversion'   => '1.0',
		      'blocking'      => true,
		      'headers'       => array('content-type' => 'application/json', 'api_key' => $api_key),
		      'cookies'       => array()
		      )
		    );
		    $response_body = json_decode($response['body']);
				//only do so once per day
				//template response

				$wordofdayhtml = '<div id="wordnik-word-of-the-day" data-url="'.$current_post_permalink.'"><div class="theword"><a href="http://www.wordnik.com/words/'.$response_body->word.'">'.$response_body->word.'</a></div>';
				$wordofdayhtml .= '<div class="word-definition">'.$response_body->definitions[0]->text.' ('.$response_body->definitions[0]->partOfSpeech.')</div>';
				$wordofdayhtml .= '<div class="word-example-head">---</div>';

				$wordofdayhtml .= '<div class="word-example">'.$response_body->examples[0]->text.'<br><span class="example-source">- <a href="'.$response_body->examples[0]->url.'">'.$response_body->examples[0]->title.'</a></span></div>';

//				$wordofdayhtml .= '<div class="word-note">'.$response_body->note.'</div>';
				$wordofdayhtml .= '<a href="http://www.wordnik.com"><div class="wordniklogo"></div></a></div>';
				echo $wordofdayhtml;
			  update_option( $option_name, $new_value );
			  update_option( 'wordofdaydata', $wordofdayhtml );
			} else {
				echo get_option('wordofdaydata');
			}
			echo $after_widget;
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( '' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}	

}

add_action( 'widgets_init', create_function( '', 'register_widget( "word_of_the_day_reverb_widget" );' ) );