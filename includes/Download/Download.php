<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * DLM_Download class.
 */
class DLM_Download {

	/** @var int */
	private $id;

	/** @var string */
	private $title;

	/** @var string */
	private $slug;

	/** @var string */
	private $status;

	/** @var int */
	private $author;

	/** @var string */
	private $description;

	/** @var string */
	private $excerpt;

	/** @var int */
	private $download_count = 0;

	/** @var bool */
	private $redirect_only = false;

	/** @var bool */
	private $featured = false;

	/** @var bool */
	private $members_only = false;

	/** @var DLM_Download_Version */
	private $version = null;

	/** @var array */
	private $versions;

	/** @var array */
	private $version_ids;

	/**
	 * @var WP_Post
	 * @deprecated 4.0
	 *
	 * Please don't use the $post variable directly anymore.
	 * The variable is left in for now for backwards compatibility but will be removed in the future!
	 */
	public $post;

	/**
	 * exists function.
	 *
	 * @todo rewrite this method. Check on ID not on post. Also exists requires specific status
	 *
	 * @access public
	 * @return bool
	 */
	public function exists() {
		return ( ! is_null( $this->post ) );
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * the_title function.
	 *
	 * @access public
	 * @return void
	 */
	public function the_title() {
		echo $this->get_title();
	}

	/**
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @param string $slug
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * @param string $status
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * @return int
	 */
	public function get_author() {
		return $this->author;
	}

	/**
	 * @param int $author
	 */
	public function set_author( $author ) {
		$this->author = $author;
	}

	/**
	 * Helper method that returns author 'display_name'
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_author() {
		$author_id = $this->get_author();
		$user      = get_user_by( 'ID', $author_id );
		if ( $user ) {
			return $user->display_name;
		}

		return '';
	}

	/**
	 * Helper method that prints author 'display_name'
	 *
	 * @access public
	 * @return void
	 */
	public function the_author() {
		echo $this->get_the_author();
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function get_excerpt() {
		return $this->excerpt;
	}

	/**
	 * Prints the excerpt
	 */
	public function the_excerpt() {
		echo $this->get_excerpt();
	}

	/**
	 * @param string $excerpt
	 */
	public function set_excerpt( $excerpt ) {
		$this->excerpt = $excerpt;
	}

	/**
	 * redirect_only function.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_redirect_only() {
		return $this->redirect_only;
	}

	/**
	 * @param bool $redirect_only
	 */
	public function set_redirect_only( $redirect_only ) {
		$this->redirect_only = $redirect_only;
	}

	/**
	 * is_featured function.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_featured() {
		return $this->featured;
	}

	/**
	 * @param bool $featured
	 */
	public function set_featured( $featured ) {
		$this->featured = $featured;
	}

	/**
	 * is_members_only function.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_members_only() {
		return $this->members_only;
	}

	/**
	 * @param bool $members_only
	 */
	public function set_members_only( $members_only ) {
		$this->members_only = $members_only;
	}

	/**
	 * @return int
	 */
	public function get_download_count() {
		if( ! $this->get_version()->is_latest() ) {
			return $this->get_version()->get_download_count();
		}else {
			return $this->download_count;
		}
	}

	/**
	 * @param int $download_count
	 */
	public function set_download_count( $download_count ) {
		$this->download_count = $download_count;
	}

	/**
	 * Get download image
	 *
	 * @param string $size
	 *
	 * @return string
	 */
	public function get_image( $size = 'full' ) {
		if ( has_post_thumbnail( $this->id ) ) {
			return get_the_post_thumbnail( $this->id, $size );
		} else {
			return '<img alt="Placeholder" class="wp-post-image" src="' . apply_filters( 'dlm_placeholder_image_src', WP_DLM::get_plugin_url() . '/assets/images/placeholder.png' ) . '" />';
		}
	}

	/**
	 * the_image function.
	 *
	 * @access public
	 *
	 * @param string $size (default: 'full')
	 *
	 * @return void
	 */
	public function the_image( $size = 'full' ) {
		echo $this->get_image( $size );
	}

	/**
	 * the_download_link function.
	 *
	 * @access public
	 * @return void
	 */
	public function the_download_link() {
		echo $this->get_the_download_link();
	}

	/**
	 * get_the_download_link function.
	 *
	 * @access public
	 * @return String
	 */
	public function get_the_download_link() {
		$scheme   = parse_url( get_option( 'home' ), PHP_URL_SCHEME );
		$endpoint = ( $endpoint = get_option( 'dlm_download_endpoint' ) ) ? $endpoint : 'download';
		$ep_value = get_option( 'dlm_download_endpoint_value' );

		switch ( $ep_value ) {
			case 'slug' :
				$value = $this->post->post_name;
				break;
			default :
				$value = $this->id;
				break;
		}

		if ( get_option( 'permalink_structure' ) ) {
			$link = home_url( '/' . $endpoint . '/' . $value . '/', $scheme );
		} else {
			$link = add_query_arg( $endpoint, $value, home_url( '', $scheme ) );
		}

		// only add version argument when current version isn't latest version
		if ( false === $this->get_version()->is_latest() ) {

			if ( $this->get_version()->has_version_number() ) {
				$link = add_query_arg( 'version', $this->get_version()->get_version_slug(), $link );
			} else {
				$link = add_query_arg( 'v', $this->get_version()->get_id(), $link );
			}
		}

		return apply_filters( 'dlm_download_get_the_download_link', esc_url_raw( $link ), $this, $this->get_version()->get_id() );
	}

	/**
	 * Version related methods
	 */

	/**
	 * @return DLM_Download_Version
	 */
	public function get_version() {

		// set latest version as current version if no version is set
		if ( $this->version == null ) {
			$versions = $this->get_versions();
			$latest   = array_shift( $versions );
			$latest->set_latest( true );
			$this->version = $latest;
		}

		return $this->version;
	}

	/**
	 * Set the download to a version other than the current / latest version it defaults to.
	 *
	 * @param DLM_Download_Version $version
	 */
	public function set_version( DLM_Download_Version $version ) {
		// check if given version is a version of this download
		if ( $version->get_download_id() == $this->get_id() ) {
			$this->version = $version;
		}
	}

	/**
	 * Get version ID by version name
	 *
	 * This used to be get_version_id(), moved to this method.
	 * @TODO: Check if this class is the correct place for this method.
	 *
	 * @param string $name
	 *
	 * @return int
	 */
	public function get_version_id_version_name( $name ) {
		$versions = $this->get_versions();

		foreach ( $versions as $version_id => $version ) {
			if ( ( is_numeric( $version->version ) && version_compare( $version->version, strtolower( $name ), '=' ) ) || sanitize_title_with_dashes( $version->version ) === sanitize_title_with_dashes( $name ) ) {
				return absint( $version_id );
			}
		}

		return 0;
	}

	/**
	 * version_exists function.
	 *
	 * @access public
	 *
	 * @param int $version_id
	 *
	 * @return bool
	 */
	public function version_exists( $version_id ) {
		return in_array( absint( $version_id ), array_keys( $this->get_versions() ) );
	}

	/**
	 * get_file_version_ids function.
	 *
	 * @access public
	 * @return array
	 */
	public function get_version_ids() {
		if ( ! is_array( $this->version_ids ) ) {
			$transient_name = 'dlm_file_version_ids_' . $this->id;

			if ( false === ( $this->version_ids = get_transient( $transient_name ) ) ) {
				$this->version_ids = get_posts( 'post_parent=' . $this->id . '&post_type=dlm_download_version&orderby=menu_order&order=ASC&fields=ids&post_status=publish&numberposts=-1' );

				set_transient( $transient_name, $this->version_ids, YEAR_IN_SECONDS );
			}
		}

		return $this->version_ids;
	}

	/**
	 * get_file_versions function.
	 *
	 * @access public
	 * @return array
	 */
	public function get_versions() {

		if ( $this->versions ) {
			return $this->versions;
		}

		$version_ids = $this->get_version_ids();

		$this->versions = array();

		foreach ( $version_ids as $version_id ) {
			$this->versions[ absint( $version_id ) ] = download_monitor()->service( 'version_factory' )->make( $version_id );
		}

		return $this->versions;
	}

	/**
	 *
	 * Deprecated methods below.
	 *
	 */

	/**
	 * You shouldn't use the post variable at all.
	 * Please use one of the available getters or setters to get the download information you're looking for.
	 *
	 * @deprecated 4.0
	 *
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * You shouldn't use the post variable at all.
	 * Please use one of the available getters or setters to get the download information you're looking for.
	 *
	 * @deprecated 4.0
	 *
	 * @param WP_Post $post
	 */
	public function set_post( $post ) {
		$this->post = $post;
	}


	/**
	 * get_the_short_description function.
	 * Deprecated, use get_excerpt() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_short_description() {
		DLM_Debug_Logger::deprecated( "DLM_Download::get_the_short_description()" );

		return $this->get_excerpt();
	}

	/**
	 * the_short_description function.
	 * Deprecated, use the_excerpt() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return void
	 */
	public function the_short_description() {
		DLM_Debug_Logger::deprecated( "DLM_Download::the_short_description()" );
		$this->the_excerpt();
	}

	/**
	 * redirect_only function.
	 * Deprecated, use is_redirect_only() instead
	 *
	 * @access public
	 *
	 * @deprecated 4.0
	 *
	 * @return bool
	 */
	public function redirect_only() {
		DLM_Debug_Logger::deprecated( "DLM_Download::redirect_only()" );

		return $this->is_redirect_only();
	}

	/**
	 * get_the_title function.
	 * Deprecated, use get_title() instead.
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_title() {
		DLM_Debug_Logger::deprecated( "DLM_Download::get_the_title()" );

		return $this->get_title();
	}

	/**
	 * get_the_image function.
	 * Deprecated, use get_image() instead.
	 *
	 * @access public
	 *
	 * @deprecated 4.0
	 *
	 * @param string $size (default: 'full')
	 *
	 * @return string
	 */
	public function get_the_image( $size = 'full' ) {
		DLM_Debug_Logger::deprecated( "DLM_Download::get_the_image()" );

		return $this->get_image( $size );
	}

	/**
	 * the_download_count function.
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return void
	 */
	public function the_download_count() {
		DLM_Debug_Logger::deprecated( "DLM_Download::the_download_count()" );

		echo $this->get_download_count();
	}

	/**
	 * get_the_download_count function.
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return int
	 */
	public function get_the_download_count() {
		DLM_Debug_Logger::deprecated( "DLM_Download::get_the_download_count()" );

		return $this->get_download_count();
	}

	/**
	 * Deprecated, use get_versions() instead
	 *
	 * @deprecated 4.0
	 *
	 * @return array
	 */
	public function get_file_versions() {
		DLM_Debug_Logger::deprecated( "DLM_Download::get_file_versions()" );

		return $this->get_versions();
	}

	/**
	 * Deprecated, use get_version_ids() instead
	 *
	 * @deprecated 4.0
	 *
	 * @return array
	 */
	public function get_file_version_ids() {
		DLM_Debug_Logger::deprecated( "DLM_Download::get_file_version_ids()" );

		return $this->get_version_ids();
	}

	/**
	 * @param string (deprecated, do not use)
	 *
	 * @return int
	 */
	public function get_version_id( $version_string = '' ) {

		DLM_Debug_Logger::deprecated( 'DLM_Download::get_version_id()' );

		if ( ! empty( $version_string ) ) {
			return $this->get_version_id_version_name( $version_string );
		}

		return 0;
	}

	/**
	 * Deprecated, use get_version() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 *
	 * @return DLM_Download_Version
	 */
	public function get_file_version() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::get_file_version()' );

		return $this->get_version();
	}

	/**
	 * Deprecated, use get_version()->get_version_number() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_version_number() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::get_the_version_number()' );

		return $this->get_version()->get_version_number();
	}

	/**
	 * Deprecated, use echo get_version()->get_version_number() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return void
	 */
	public function the_version_number() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::the_version_number()' );

		echo $this->get_version()->get_version_number();
	}

	/**
	 * Deprecated, use get_version()->has_version_number() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return bool
	 */
	public function has_version_number() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::has_version_number()' );

		return $this->get_version()->has_version_number();
	}

	/**
	 * Deprecated, use get_version()->get_filename() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_filename() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::get_the_filename()' );

		return $this->get_version()->get_filename();
	}

	/**
	 * Deprecated, use echo get_version()->get_filename() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return void
	 */
	public function the_filename() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::get_the_filename()' );

		echo $this->get_version()->get_filename();
	}

	/**
	 * Deprecated, use echo get_version()->get_date() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_file_date() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::get_the_filename()' );

		return $this->get_version()->get_date();
	}

	/**
	 * Deprecated, use get_version()->get_filesize_formatted() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_filesize() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::get_the_filesize()' );

		return $this->get_version()->get_filesize_formatted();
	}

	/**
	 * Deprecated, use echo get_version()->get_filesize_formatted() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return void
	 */
	public function the_filesize() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::the_filesize()' );

		echo $this->get_version()->get_filesize_formatted();
	}

	/**
	 * Deprecated, use get_version()->get_md5() (or hash you like) instead
	 *
	 * @deprecated 4.0
	 *
	 * @param  string $type md5, sha1 or crc32
	 *
	 * @return string
	 */
	public function get_the_hash( $type = 'md5' ) {
		DLM_Debug_Logger::deprecated( 'DLM_Download::get_the_hash()' );

		if ( method_exists( $this->get_version(), "get_" . $type ) ) {
			return call_user_func( array( $this->get_version(), "get_" . $type ) );
		}

		return "";
	}

	/**
	 * Deprecated, use echo get_version()->get_md5() (or hash you like) instead
	 *
	 * @deprecated 4.0
	 *
	 * @param  string $type md5, sha1 or crc32
	 *
	 * @return string
	 */
	public function the_hash( $type = 'md5' ) {
		DLM_Debug_Logger::deprecated( 'DLM_Download::the_hash()' );

		if ( method_exists( $this->get_version(), "get_" . $type ) ) {
			echo call_user_func( array( $this->get_version(), "get_" . $type ) );
		}
	}

	/**
	 * Deprecated, use get_version()->get_filetype() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_filetype() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::get_the_filetype()' );

		return $this->get_version()->get_filetype();
	}

	/**
	 * Deprecated, use echo get_version()->get_filetype() instead
	 *
	 * @deprecated 4.0
	 *
	 * @access public
	 * @return void
	 */
	public function the_filetype() {
		DLM_Debug_Logger::deprecated( 'DLM_Download::the_filetype()' );

		echo $this->get_version()->get_filetype();
	}
}
