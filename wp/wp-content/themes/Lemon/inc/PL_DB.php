<?php
/*

per
percent

*/

function create_ref_tables(){
    global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
    $charset = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql = "CREATE TABLE {$wordpress_prefix}pl_ref_info(
        id INT NOT NULL AUTO_INCREMENT,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        ref_code VARCHAR(50) NOT NULL,
        commission_type VARCHAR(20) NOT NULL,
        commission_rate FLOAT NOT NULL,
        expire_date TIMESTAMP NULL,
        create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(id),
        UNIQUE (email),
        UNIQUE (ref_code)
    ) ENGINE = MyISAM,{$charset};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function remove_ref_tables(){
    $sql = "DROP TABLE pl_ref_info";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function create_ref_ordered(){
    global $wpdb;
	$wordpress_prefix = $wpdb->prefix;
    $charset = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql = "CREATE TABLE  {$wordpress_prefix}pl_ref_ordered (
        id INT(11) NOT NULL auto_increment,
        ref_info_id varchar(150) NOT NULL,
        order_id varchar(150) NOT NULL ,
        client_id varchar(20) NOT NULL ,
        sub_total_price varchar(150) NOT NULL,
        create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)) ENGINE=MyISAM,{$charset};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function remove_ref_ordered(){
    $sql = "DROP TABLE pl_ref_ordered";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

?>