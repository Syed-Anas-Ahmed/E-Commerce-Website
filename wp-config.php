<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wscubetech' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'mH5u/vvyKC?]L+<HAa.MI&ptW,X6A:pgr%CmhNKgz3ee4Sqt>DPb oMDc`X48SJ4' );
define( 'SECURE_AUTH_KEY',  'w=+ j~tP&>_L-oFM8k/WI=Ntc$<sQLO@us}Isj+JO?wptI eGOAzo#er}QpGd0}]' );
define( 'LOGGED_IN_KEY',    '{Da0 Q;BrEQf1(f2vNfCyBo!yPT{t2}V{&7G5t]QWC:PY|bnhL9 h/fS<=#;8A8E' );
define( 'NONCE_KEY',        ']@~vUEr9FJDsy>olUGcN}cW:}fbA7$#Jhi8Y75]3:vd>}VQ&UGSO-mSPQ4Drnpio' );
define( 'AUTH_SALT',        'bBQqlO8V(<JmK5CWN}7JQIxnl}aaq-h6r[BsDmyzJ6p5{P1(/|x`xWDkfO6>MvQk' );
define( 'SECURE_AUTH_SALT', 'jk,dE<c-Jg]#,mcQ <l#)sw/I|z*UW{Y*[mSt#/Jc6Z^XG575Fe!I+~?5IEUX|M<' );
define( 'LOGGED_IN_SALT',   '^xm.^2>06q5X4#xb^Fx7rD$A573MY[SN+Xw&MH!m6>H7FzwtPmY^vW|:afmtfEf<' );
define( 'NONCE_SALT',       'HB6SEej|kK-$jjK).@&;CdlDwZ.<kJy{^}WeYU$C2bQvD0zsSq->mSy/^lfjzRcJ' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
