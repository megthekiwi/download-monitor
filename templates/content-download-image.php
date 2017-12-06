<?php
/**
 * Just image and download count
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<aside class="download-box">

	<a href="<?php $dlm_download->the_download_link(); ?>"><?php $dlm_download->the_image(); ?></a>

	<div
		class="download-count"><?php printf( _n( '1 download', '%d downloads', $dlm_download->get_the_download_count(), 'download-monitor' ), $dlm_download->get_the_download_count() ) ?></div>

	
</aside>
