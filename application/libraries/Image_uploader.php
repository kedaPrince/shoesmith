<?php
class Image_uploader {
	private $allowed_types          = array('jpg','jpeg','png','gif', 'webp');
	private $max_filesize           = 20;    // Maximum file size in MB
	private $max_width              = 5000;  // Max width allowed for the large image
	private $max_height             = 0;    // Max height allowed for the large image
	private $image_sizes            = array();
	private $upload_path            = "resources/uploads/images/";
	private $preview_width          = "400";
	private $preview_height         = "300";
	private $large_image_prefix     = "actual_";    // The prefix name to large image
	private $thumb_image_prefix     = "crop_";
	private $files = array();

	public function __construct($params=array()) {

		//Overides
		foreach ($params as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function createThumbnailImage($thumb_image_name, $image, $width, $height=false) {
	    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	    $imageType = image_type_to_mime_type((int)$imageType);

	    // If height isn't specified then work out height based on ratio
	    if ($height === false) {
	        $ratio = $imageheight / $imagewidth;
	        $height = $width * $ratio;
	    }

	    $newImageWidth = $width;
	    $newImageHeight = $height;

	    if ($imagewidth > $width || $imageheight > $height) {
	        $imagePortrait = ($imagewidth < $imageheight) ? true : false;
	        $containerPortrait = ($width < $height) ? true : false;

	        if ($imagePortrait) {
	            if ($imageheight > $height) {
	                $newImageWidth = $imagewidth / $imageheight * $height;
	                $newImageHeight = $height;
	            } else {
	                $newImageHeight = $imageheight / $imagewidth * $width;
	                $newImageWidth = $width;
	            }
	        } else {
	            if ($imagewidth > $width) {
	                $newImageHeight = $imageheight / $imagewidth * $width;
	                $newImageWidth = $width;
	            } else {
	                $newImageWidth = $imagewidth / $imageheight * $height;
	                $newImageHeight = $height;
	            }
	        }
	    }

	    // Round dimensions to avoid decimals
	    $newImageWidth = round($newImageWidth);
	    $newImageHeight = round($newImageHeight);

	    $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);

	    // Handle transparency
	    switch ($imageType) {
	        case "image/gif":
	        case "image/png":
	        case "image/x-png":
	            $background = imagecolorallocate($newImage, 0, 0, 0);
	            imagecolortransparent($newImage, $background);
	            imagealphablending($newImage, false);
	            imagesavealpha($newImage, true);
	            break;
	        case "image/webp":
	            // WebP also supports transparency
	            imagealphablending($newImage, false);
	            imagesavealpha($newImage, true);
	            break;
	    }

	    // Create image from source based on type
	    switch ($imageType) {
	        case "image/gif":
	            $source = imagecreatefromgif($image);
	            break;
	        case "image/jpeg":
	        case "image/jpg":
	        case "image/pjpeg":
	            $source = imagecreatefromjpeg($image);
	            break;
	        case "image/png":
	        case "image/x-png":
	            $source = imagecreatefrompng($image);
	            break;
	        case "image/webp":
	            $source = imagecreatefromwebp($image);
	            break;
	    }

	    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $imagewidth, $imageheight);

	    // Save thumbnail based on type
	    switch ($imageType) {
	        case "image/gif":
	            imagegif($newImage, $thumb_image_name);
	            break;
	        case "image/jpeg":
	        case "image/jpg":
	        case "image/pjpeg":
	            imagejpeg($newImage, $thumb_image_name, 70);
	            break;
	        case "image/png":
	        case "image/x-png":
	            imagepng($newImage, $thumb_image_name, 7);
	            break;
	        case "image/webp":
	            imagewebp($newImage, $thumb_image_name, 80); // 80 is the quality setting
	            break;
	    }

	    chmod($thumb_image_name, 0777);
	    return $thumb_image_name;
	}


	function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
	    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	    $imageType = image_type_to_mime_type($imageType);
	    if ($height == 0 || $width == 0)
	        list($width, $height) = getimagesize($image);
	    $newImageWidth = ceil($width * $scale);
	    $newImageHeight = ceil($height * $scale);
	    $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
	    switch ($imageType) {
	        case "image/gif":
	            $source = imagecreatefromgif($image);
	            break;
	        case "image/pjpeg":
	        case "image/jpeg":
	        case "image/jpg":
	            $source = imagecreatefromjpeg($image);
	            break;
	        case "image/png":
	        case "image/x-png":
	            $source = imagecreatefrompng($image);
	            break;
	    }
	    imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
	    switch ($imageType) {
	        case "image/gif":
	            imagegif($newImage, $thumb_image_name);
	            break;
	        case "image/pjpeg":
	        case "image/jpeg":
	        case "image/jpg":
	            imagejpeg($newImage, $thumb_image_name, 100);
	            break;
	        case "image/png":
	        case "image/x-png":
	            imagepng($newImage, $thumb_image_name);
	            break;
	    }
	    chmod($thumb_image_name, 0777);
	    return $thumb_image_name;
	}

	public function upload() {
		$imageData = array();
		if (isset($_FILES['imagefile'])) {
			$i = 0;
			$imageError = 0;
			foreach ($_FILES['imagefile']['tmp_name'] as $key => $tmp_name) {
				//Get the file information
				$filename       = basename($_FILES['imagefile']['name'][$key]);
				$imageData[$i] = array(
					'name'      => $_FILES['imagefile']['name'][$key],
					'temp_name' => $_FILES['imagefile']['tmp_name'][$key],
					'size'      => $_FILES['imagefile']['size'][$key],
					'type'      => $_FILES['imagefile']['type'][$key],
					'basename'  => $filename,
					'file_ext'  => strtolower(substr($filename, strrpos($filename, '.') + 1)),
					'error'     => array()
				);
				if ($_FILES['imagefile']['error'][$key] == 0) {
					//Check file type
					if (!in_array($imageData[$i]['file_ext'], $this->allowed_types)) {
						$imageData[$i]['error'][] = $imageData[$i]['file_ext'].' ('.$imageData[$i]['type'].') files are not allowed.';
						$imageError = 1;
					}

					//check if the file size is above the allowed limit
					if ($imageData[$i]['size'] > ($this->max_filesize * 1048576)) {
						$imageData[$i]['error'][] = "Image filesize is greater than the allowed " . $this->max_filesize . "MB";
						$imageError = 1;
					}

					//If there are no errors, then proceed to save the file
					if (empty($imageData[$i]['error'])) {
						$upload_path                    = $this->upload_path;
						$imageData[$i]['hashed_name']   = $this->generate_filename($upload_path, $imageData[$i]['file_ext']);
						$upload_file_location           = $upload_path . $imageData[$i]['hashed_name'];
						if (move_uploaded_file($imageData[$i]['temp_name'], $upload_file_location)) {
							$imageData[$i]['success'] = 1;

							//Create Different Image Sizes
							if (!empty($this->image_sizes)){
								foreach ($this->image_sizes as $folder => $size) {
									dir_create($upload_path.$folder.'/');
									$thumb_file_name = $upload_path.$folder.'/'.$imageData[$i]['hashed_name'];
									$this->createThumbnailImage($thumb_file_name, $upload_file_location, $size['width'], $size['height']);
								}
							}
						}
						else {
							$imageData[$i]['error'] = "Failed to move uploaded file to upload directory.";
							$imageData[$i]['success'] = 0;
							$imageError = 1;
						}
					}
				}
				else {
					$imageData[$i]['error'][] = $this->codeToMessage($_FILES['imagefile']['error'][$key]);
					$imageError = 1;
				}
				$i++;
			}
		} else {
			$error = "Select an image for upload<br />";
		}

		if (!empty($error)) {
			return array(
				'success'   => false,
				'error'     => $error
			);
		}
		else if (!empty($imageData)) {
			return array(
				'success'       => true,
				'image_error'   => $imageError,
				'image_data'    => $imageData,
				'path'          => $this->upload_path
			);
		}
		else {
			return array(
				'success'   => false,
				'error'     => 'Failed to process image data.'
			);
		}

	}

	public function saved_edited_image() {
		if (isset($_POST["upload_thumbnail"])) {

			$filename = $_POST['filename'];

			$large_image_location = $upload_path . $_POST['filename'];
			$thumb_image_location = $upload_path . $thumb_image_prefix . $_POST['filename'];

			$x1 = $_POST["x1"];
			$y1 = $_POST["y1"];
			$x2 = $_POST["x2"];
			$y2 = $_POST["y2"];
			$w = $_POST["w"];
			$h = $_POST["h"];
			$degrees = $_POST["degrees"];
			$scale = 1;
			list($imagewidth, $imageheight, $imageType) = getimagesize($large_image_location);
			$imageType = image_type_to_mime_type($imageType);
			switch ($imageType) {
				case "image/gif":
					$source = imagecreatefromgif($large_image_location);
					$rotate = imagerotate($source, $degrees, 0);
					imagegif($rotate, $large_image_location); //save the new image
					break;
				case "image/pjpeg":
				case "image/jpeg":
				case "image/jpg":
					$source = imagecreatefromjpeg($large_image_location);
					$rotate = imagerotate($source, $degrees, 0);
					imagejpeg($rotate, $large_image_location); //save the new image
					break;
				case "image/png":
				case "image/x-png":
					$source = imagecreatefrompng($large_image_location);
					$rotate = imagerotate($source, $degrees, 0);
					imagepng($rotate, $large_image_location); //save the new image
					break;
			}

			imagedestroy($source); //free up the memory
			imagedestroy($rotate);  //free up the memory

			$cropped = $this->image_uploader->resizeThumbnailImage($thumb_image_location, $large_image_location, $w, $h, $x1, $y1, $scale);
			foreach ($image_thumbnails as $key => $value) {
				if (!is_dir($upload_path . $key)) {
					mkdir($upload_path . $key, 0777);
					chmod($upload_path . $key, 0777);
				}
				$thumb_file_name = $upload_path . $key . '/' . $thumb_image_prefix . $filename;
				$this->image_uploader->createThumbnailImage($thumb_file_name, $thumb_image_location, $value[0], $value[1]);
			}
		}
	}

	private function generate_filename($filepath, $ext) {

		while (true) {
			$name = substr(md5(uniqid() . time()), 0, 12) . "." . $ext;
			if (!file_exists($filepath.'/'.$name)) {
				break;
			}
		}

		return $name;
	}

	private function codeToMessage($code) {
		$message = null;

		switch ($code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = "The uploaded file was only partially uploaded";
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = "No file was uploaded";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = "Missing a temporary folder";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = "Failed to write file to disk";
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = "File upload stopped by extension";
				break;
			default:
				$message = "Unknown upload error";
				break;
		}

		return $message;
	}


}
?>