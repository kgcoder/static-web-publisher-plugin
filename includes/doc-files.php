<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function stwbpb_send_doc_file($path){
   $allowed_extensions = ['hdoc','cdoc','condoc'];
   if ($path) {

       $base_dir = ABSPATH . 'static-documents';
       $file = ltrim($path, '/');

       $real_path = realpath($base_dir . '/' . $file);
       
       $ext = pathinfo($real_path, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed_extensions)) {
            wp_die('File type not allowed');
        }
        if (!$real_path || strpos($real_path, $base_dir) !== 0 || !file_exists($real_path)) {
            wp_die('File not found or access denied');
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: inline');
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
        readfile($real_path);
        exit;
    }
}


