<?php


function custom_theme_customizer($wp_customize) {
    
    $wp_customize->add_section('color_options', array(
        'title' => 'Theme Colour Options',
        'priority' => 30,
    ));

    $wp_customize->add_setting('MAIN_BG_COLOR', array(
        'default' => '#FFF',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'MAIN_BG_COLOR', array(
        'label' =>'Backgorund Colour',
        'section' => 'color_options',
        'settings' => 'MAIN_BG_COLOR'
    )));

    $wp_customize->add_setting('MAIN_COLOR', array(
        'default' => '#1B5E20',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'MAIN_COLOR', array(
        'label' => 'Main Colour',
        'section' => 'color_options',
        'settings' => 'MAIN_COLOR',
    )));

    $wp_customize->add_setting('MAIN_COLOR_HOVER', array(
        'default' => '#14532D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'MAIN_COLOR_HOVER', array(
        'label' => 'Main Colour Hover',
        'section' => 'color_options',
        'settings' => 'MAIN_COLOR_HOVER',
    )));

    $wp_customize->add_setting('MAIN_COLOR_MOBILE', array(
        'default' => '#1B5E20',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'MAIN_COLOR_MOBILE', array(
        'label' => 'Main Colour Mobile',
        'section' => 'color_options',
        'settings' => 'MAIN_COLOR_MOBILE',
    )));

    $wp_customize->add_setting('MAIN_COLOR_FOCUS', array(
        'default' => '#14532D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'MAIN_COLOR_FOCUS', array(
        'label' => 'Main Colour Focus',
        'section' => 'color_options',
        'settings' => 'MAIN_COLOR_FOCUS',
    )));

    $wp_customize->add_setting('MAIN_COLOR_ACTIVE', array(
        'default' => '#14532D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'MAIN_COLOR_ACTIVE', array(
        'label' => 'Main Colour Active',
        'section' => 'color_options',
        'settings' => 'MAIN_COLOR_ACTIVE',
    )));

    $wp_customize->add_setting('MAIN_COLOR_CLOUD', array(
        'default' => '#7bb026',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'MAIN_COLOR_CLOUD', array(
        'label' => 'Main Colour Cloud',
        'section' => 'color_options',
        'settings' => 'MAIN_COLOR_CLOUD',
    )));

    $wp_customize->add_setting('SECOND_COLOR_CLOUD', array(
        'default' => '#c7ea8f',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'SECOND_COLOR_CLOUD', array(
        'label' => 'Second Colour Cloud',
        'section' => 'color_options',
        'settings' => 'SECOND_COLOR_CLOUD',
    )));
}

add_action('customize_register', 'custom_theme_customizer');

// define( 'MAIN_BG_COLOR', get_theme_mod("MAIN_BG_COLOR","#FFF"));
// define( 'MAIN_COLOR', get_theme_mod("MAIN_COLOR",'#1B5E20'));
// define( 'MAIN_COLOR_HOVER', get_theme_mod("MAIN_COLOR_HOVER",'#14532D'));
// define( 'MAIN_COLOR_FOCUS',get_theme_mod("MAIN_COLOR_FOCUS",'#14532D'));
// define( 'MAIN_COLOR_ACTIVE',get_theme_mod("MAIN_COLOR_ACTIVE",'#14532D'));
// define( 'MAIN_COLOR_MOBILE',get_theme_mod("MAIN_COLOR_MOBILE",'#1B5E20'));

define( 'MAIN_BG_COLOR', get_theme_mod("MAIN_BG_COLOR",'#FFF'));
define( 'MAIN_COLOR', get_theme_mod("MAIN_COLOR",'#1B5E20'));
define( 'MAIN_COLOR_HOVER', get_theme_mod("MAIN_COLOR_HOVER",'#14532D'));
define( 'MAIN_COLOR_FOCUS',get_theme_mod("MAIN_COLOR_FOCUS",'#14532D'));
define( 'MAIN_COLOR_ACTIVE',get_theme_mod("MAIN_COLOR_ACTIVE",'#14532D'));
define( 'MAIN_COLOR_MOBILE',get_theme_mod("MAIN_COLOR_MOBILE",'#1B5E20'));
define( 'MAIN_COLOR_CLOUD',get_theme_mod("MAIN_COLOR_CLOUD",'#7bb026'));
define( 'SECOND_COLOR_CLOUD',get_theme_mod("SECOND_COLOR_CLOUD",'#c7ea8f'));
?>