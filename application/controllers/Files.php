<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Files extends MY_Controller {

    private function set_cache($seconds_to_cache)
    {
        $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=$seconds_to_cache");
    }

    /**
     * TODO: This function still needs to adjust for regenerating images for new image uploader
     * (At the moment it's useless)
     */
    public function resize_images($size=200) {
        // ============ ADDED CSRF PROTECTION ============
        // Since this is a state-changing operation (creates files), we need CSRF protection
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            show_error('Invalid CSRF token', 400);
            return;
        }
        // ============ END CSRF PROTECTION ============
        
        $this->load->library('image_uploader');
        $path = abs_path().'resources/uploads/';

        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ('.' === $file) continue;
                if ('..' === $file) continue;
                if ('.htaccess' === $file) continue;

                echo '<h2>'.$file.'</h2>';
                if (is_dir($path.$file)) {
                    $path2 = $path.$file;
                    if ($handle2 = opendir($path2)) {
                        while (false !== ($file2 = readdir($handle2))) {
                            if ('.' === $file2) continue;
                            if ('..' === $file2) continue;
                            if ('.htaccess' === $file2) continue;
                            echo '<h3>'.$file2.'</h3>';

                            if (is_dir($path2.'/'.$file2)) {
                                $path3 = $path2.'/'.$file2;
                                if ($handle3 = opendir($path3)) {
                                    while (false !== ($file3 = readdir($handle3))) {
                                        if ('.' === $file3) continue;
                                        if ('..' === $file3) continue;
                                        if ('.htaccess' === $file3) continue;
                                        if (is_numeric($file3)) continue;

                                        $thumb = $path3.'/'.$size.'/'.$file3;
                                        dir_create('resources/uploads/'.$file.'/'.$file2.'/'.$size);

                                        if (!file_exists($thumb)) {
                                            // do something with the file
                                            $this->image_uploader->createThumbnailImage($thumb, $path3.'/'.$file3, $size);
                                            echo $file3.' - CREATED THUMB';
                                            echo '<br/>';
                                        }
                                        
                                    }
                                }
                            }

                            echo '<br/>';
                        }
                    }
                }

                
            }
            closedir($handle);
        }
    }

    public function image($module, $year, $month, $id, $file, $size=false) {

        $fullpath = abs_path().'resources/uploads/images/'.$module.'/'.$year.'/'.$month.'/'.$id.'/'.($size?$size.'/':'').$file;
        if (file_exists($fullpath)) {

            $out = file_get_contents($fullpath);
            if ($this->uri->segment(1,'')=='download-image') {
                header("Content-Disposition: attachment; filename=$file");
            }
            if ($fd = fopen ($fullpath, "r")) {
                $fsize = filesize($fullpath);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                switch (strtolower($ext)) {
                    case "gif":
                        $this->output->set_content_type('image/gif');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    case "jpeg":
                        $this->output->set_content_type('image/jpg');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    case "jpg":
                        $this->output->set_content_type('image/jpg');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    case "png":
                        $this->output->set_content_type('image/png');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    case "webp":
                        $this->output->set_content_type('image/webp');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    default:
                        $out = file_get_contents(abs_path().'resources/no_image/general.png');
                        $this->output->set_content_type('image/png');
                }
            }
            fclose ($fd);
            $this->output->set_output($out);
        }
        else {
            $out = file_get_contents(abs_path().'resources/no_image/general.png');
            $this->output->set_content_type('image/png');
            $this->output->set_output($out);
        }
    }

    public function download_image($module, $year, $month, $id, $file, $size=false) {

        $fullpath = abs_path().'resources/uploads/images/'.$module.'/'.$year.'/'.$month.'/'.$id.'/'.($size?$size.'/':'').$file;
        if (file_exists($fullpath)) {

            $out = file_get_contents($fullpath);
            
            header("Content-Disposition: attachment; filename=$file");

            if ($fd = fopen ($fullpath, "r")) {
                $fsize = filesize($fullpath);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                switch (strtolower($ext)) {
                    case "gif":
                        $this->output->set_content_type('image/gif');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    case "jpeg":
                        $this->output->set_content_type('image/jpg');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    case "jpg":
                        $this->output->set_content_type('image/jpg');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    case "png":
                        $this->output->set_content_type('image/png');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    case "webp":
                        $this->output->set_content_type('image/webp');
                        $this->set_cache(100000); // necasarry for chrome or it will not cache at all, not even on the same page
                        break;
                    default:
                        $out = file_get_contents(abs_path().'resources/no_image/general.png');
                        $this->output->set_content_type('image/png');
                }
            }
            fclose ($fd);
            $this->output->set_output($out);
        }
        else {
            $out = file_get_contents(abs_path().'resources/no_image/general.png');
            $this->output->set_content_type('image/png');
            $this->output->set_output($out);
        }
    }

    public function file($module, $year, $month, $id, $file) {
        $fullpath = abs_path() . 'resources/uploads/files/' . $module . '/' . $year . '/' . $month . '/' . $id . '/' . $file;
        if (file_exists($fullpath)) {
            $fsize = filesize($fullpath);
            $mime = mime_content_type($fullpath);  // Correct usage of mime_content_type
            $this->output->set_content_type($mime);

            // Check if the download segment is present in the URL
            if ($this->uri->segment(1, '') == 'download') {
                header("Content-Disposition: attachment; filename=\"$file\"");
                header('Content-Length: ' . $fsize);
            }

            // Open the file and stream it directly without loading into memory
            if ($fd = fopen($fullpath, "rb")) {  // Open in binary mode for compatibility
                $this->set_cache(100000);  // Set cache if needed, though this might need to be before output starts
                while (!feof($fd)) {
                    echo fread($fd, 8192);
                    flush();  // Flush output buffer to prevent memory bloat
                }
                fclose($fd);
            }
        } else {
            show_404();
        }
    }


}