<?php

/**
 * Plugin Name:       Narola Weather Widget
 * Plugin URI:        http://www.narolainfotech.com
 * Description:       Display weather widgets.
 * Version:           1.0.0
 * Author:            narolainfotech
 * Author URI:        http://www.narolainfotech.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       narola-weather-widget
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_VERSION', '1.0.0' );

function giftmyhoneymoon_scripts() {

    wp_enqueue_style( 'narola_weather', plugin_dir_url( __FILE__ ) . 'css/narola-weather-widget.css', array(), '1.0.0', 'all' );
    
}
add_action( 'wp_enqueue_scripts', 'giftmyhoneymoon_scripts' );

class my_weather_widget extends WP_Widget {
    
    /**
    * Sets up the widgets name etc
    */
    public function __construct() {
            $widget_ops = array( 
                    'classname' => 'weather_widget',
                    'description' => 'Weather Widget.',
            );
            parent::__construct( 'my_weather_widget', 'Weather Widget', $widget_ops );
    }
    
    /**
    * Outputs the options form on admin
    *
    * @param array $instance The widget options
    */
    public function form($instance) {

        // Check values
        if( $instance) {
            $title = esc_attr($instance['title']);
            $location = $instance['location'];
        } else {
            $title = '';
            $location = '';
        }
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp_widget_plugin'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('location'); ?>"><?php _e('Location:', 'wp_widget_plugin'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('location'); ?>" name="<?php echo $this->get_field_name('location'); ?>" type="text" value="<?php echo $location; ?>" />
        </p>
    <?php
    }
    
    /**
    * Processing widget options on save
    *
    * @param array $new_instance The new options
    * @param array $old_instance The previous options
    *
    * @return array
    */
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        // Fields
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['location'] = strip_tags($new_instance['location']);
        return $instance;
    }
    
    /**
    * Outputs the content of the widget
    *
    * @param array $args
    * @param array $instance
    */
    public function widget($args, $instance) {
        extract( $args );

        // these are the widget options
        $title = apply_filters('widget_title', $instance['title']);
        $location = $instance['location'];
        echo $before_widget;

        // Display the widget
        echo '<div class="widget-text wp_widget_plugin_box">';
        
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );
        
            if( $location ) {
                $api = wp_remote_get("http://api.openweathermap.org/data/2.5/weather?q=".$location."&appid=f16f79f6758489d249e65be7d78af2a4");
                $json = wp_remote_retrieve_body($api);
                $data = json_decode($json);                
                
                echo '<div class="widget-location">';
                    echo '<span>';
                        echo '<div id="weather-widget" class="weather-widget">';
                            echo '<h2 class="weather-widget__city-name">'.$title.'</h2>';
                            echo '<h3 class="weather-widget__temperature"><img src="https://openweathermap.org/img/w/'.$data->weather[0]->icon.'.png" alt="Weather '.$data->name.' , '.$data->sys->country.'" width="50" height="50">'.k_to_c($data->main->temp).' &#x2103</h3>';                            
                            echo '<p class="weather-widget__main">'.$data->weather[0]->description.'</p>';
                            echo '<table class="weather-widget__items">';
                                echo '<tbody>';
                                    echo '<tr class="weather-widget__item">';
                                        echo '<td>Wind</td>';
                                        echo '<td id="weather-widget-wind">Moderate breeze,'.$data->wind->speed.' m/s,North-northeast ( '.$data->wind->deg.' )</td>';
                                    echo '</tr>';
                                    echo '<tr class="weather-widget__item">';
                                        echo '<td>Cloudiness</td>';
                                        echo '<td id="weather-widget-cloudiness">Sky is '.$data->weather[0]->main.'</td>';
                                    echo '</tr>';
                                    echo '<tr class="weather-widget__item">';
                                        echo '<td>Pressure</td>';
                                        echo '<td>'.$data->main->pressure.' hpa</td>';
                                    echo '</tr>';
                                    echo '<tr class="weather-widget__item">';
                                        echo '<td>Humidity</td>';
                                        echo '<td>'.$data->main->humidity.' %</td>';
                                    echo '</tr>';
                                    echo '<tr class="weather-widget__item">';
                                        echo '<td>Sunrise</td>';
                                        echo '<td>'.date('H:i',$data->sys->sunrise).' </td>';
                                    echo '</tr>';
                                    echo '<tr class="weather-widget__item">';
                                        echo '<td>Sunset</td>';
                                        echo '<td>'.date('H:i',$data->sys->sunset).'</td>';
                                    echo '</tr>';
                                    echo '<tr class="weather-widget__item">';
                                        echo '<td>Geo coords</td>';
                                        echo '<td>[<span id="wrong-data-lat">'.$data->coord->lat.'</span>,&nbsp;<span id="wrong-data-lon">'.$data->coord->lon.'</span>]</td>';
                                    echo '</tr>';
                                echo '</tbody>';
                            echo '</table>';
                        echo '</div>';
                    echo '</span>';
                echo '</div>';
            }else{
                echo '<p>Please enter location</p>';
            }
            
            
        echo '</div>';
        echo $after_widget;
    }
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("my_weather_widget");'));

/**
 * Convert Kelvin temperatures to Celsius
 * 
 * @param type $temp
 * @return boolean
 */
function k_to_c($temp) {
    if ( !is_numeric($temp) ) { return false; }
    return round(($temp - 273.15));
}