<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function delete_img(){
		extract($_POST);
		if(is_file($path)){
			if(unlink($path)){
				$resp['status'] = 'success';
			}else{
				$resp['status'] = 'failed';
				$resp['error'] = 'failed to delete '.$path;
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = 'Unkown '.$path.' path';
		}
		return json_encode($resp);
	}

	function request_claim(){
		extract($_POST);
		if(empty($item_id) || empty($name)){
			$resp['status']='failed';
			$resp['msg']='Item and your name are required.';
			return json_encode($resp);
		}
		// ensure table exists
		$this->conn->query("CREATE TABLE IF NOT EXISTS `claim_requests` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`item_id` bigint(20) NOT NULL,
			`name` text NOT NULL,
			`contact` text DEFAULT NULL,
			`message` text DEFAULT NULL,
			`status` tinyint(1) NOT NULL DEFAULT 0,
			`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`approved_by` int(11) DEFAULT NULL,
			`approved_at` datetime DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `item_id` (`item_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

		$item_id = $this->conn->real_escape_string($item_id);
		$name = $this->conn->real_escape_string($name);
		$contact = isset($contact) ? $this->conn->real_escape_string($contact) : '';
		$message = isset($message) ? $this->conn->real_escape_string($message) : '';

		$sql = "INSERT INTO `claim_requests` (`item_id`,`name`,`contact`,`message`) VALUES ('{$item_id}','{$name}','{$contact}','{$message}')";
		$ins = $this->conn->query($sql);
		if($ins){
			$resp['status']='success';
			$resp['msg']='Your claim request has been submitted.';
		}else{
			$resp['status']='failed';
			$resp['msg']='Failed to submit request: '.$this->conn->error;
		}
		return json_encode($resp);
	}

	function approve_claim(){
		extract($_POST);
		if(empty($id)){
			$resp['status']='failed';
			$resp['msg']='Claim ID is required.';
			return json_encode($resp);
		}
		// fetch claim
		$qry = $this->conn->query("SELECT * FROM `claim_requests` where id = '{$id}'");
		if(!$qry || $qry->num_rows == 0){
			$resp['status']='failed';
			$resp['msg']='Claim request not found.';
			return json_encode($resp);
		}
		$claim = $qry->fetch_assoc();
		$approved_by = $this->settings->userdata('id') ?? NULL;
		$update = $this->conn->query("UPDATE `claim_requests` set `status` = 1, `approved_by` = " . ($approved_by? (int)$approved_by : 'NULL') . ", `approved_at` = CURRENT_TIMESTAMP where id = '{$id}'");
		if($update){
			// mark item as claimed (status = 2)
			$this->conn->query("UPDATE `item_list` set `status` = 2 where id = '{$claim['item_id']}'");
			$resp['status']='success';
			$resp['msg']='Claim approved and item marked as claimed.';
			$this->settings->set_flashdata('success',$resp['msg']);
		}else{
			$resp['status']='failed';
			$resp['msg']='Failed to approve claim: '.$this->conn->error;
		}
		return json_encode($resp);
	}
	function save_category(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				if(!empty($data)) $data .=",";
				$v = htmlspecialchars($this->conn->real_escape_string($v));
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$check = $this->conn->query("SELECT * FROM `category_list` where `name` = '{$name}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "category Name already exists.";
			return json_encode($resp);
			exit;
		}
		if(empty($id)){
			$sql = "INSERT INTO `category_list` set {$data} ";
		}else{
			$sql = "UPDATE `category_list` set {$data} where id = '{$id}' ";
		}
			$save = $this->conn->query($sql);
		if($save){
			$sid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['sid'] = $sid;
			$resp['status'] = 'success';
			if(empty($id))
				$resp['msg'] = "New Category successfully saved.";
			else
				$resp['msg'] = " Category successfully updated.";
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		if($resp['status'] == 'success')
			$this->settings->set_flashdata('success',$resp['msg']);
			return json_encode($resp);
	}
	function delete_category(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `category_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Category successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
		function approve_item(){
			extract($_POST);
			if(empty($id)){
				$resp['status']='failed';
				$resp['msg']='Item ID is required.';
				return json_encode($resp);
			}
			$update = $this->conn->query("UPDATE `item_list` set `status` = 1 where id = '{$id}'");
			if($update){
				$resp['status']='success';
				$resp['msg']='Item has been approved and published.';
				$this->settings->set_flashdata('success',$resp['msg']);
			}else{
				$resp['status']='failed';
				$resp['msg']='Failed to update status: '.$this->conn->error;
			}
			return json_encode($resp);
		}
	function save_inquiry(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id', 'visitor'))){
				if(!empty($data)) $data .=",";
				$v = htmlspecialchars($this->conn->real_escape_string($v));
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `inquiry_list` set {$data} ";
		}else{
			$sql = "UPDATE `inquiry_list` set {$data} where id = '{$id}' ";
		}
			$save = $this->conn->query($sql);
		if($save){
			$resp['status'] = 'success';
			if(empty($id)){
				if(!isset($visitor))
					$resp['msg'] = "New Inquiry successfully saved.";
					else
					$resp['msg'] = "Your Message has been sent successfully. We will reach out using your contact information as soon as we sees your message. Thank you!";
			}else
				$resp['msg'] = " Inquiry successfully updated.";
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		if($resp['status'] == 'success')
			$this->settings->set_flashdata('success',$resp['msg']);
			return json_encode($resp);
	}
	function delete_inquiry(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `inquiry_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Inquiry successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_item(){
		extract($_POST);

		// ensure item_type column exists (adds it if missing)
		$col = $this->conn->query("SHOW COLUMNS FROM `item_list` LIKE 'item_type'")->num_rows;
		if($col == 0){
			$this->conn->query("ALTER TABLE `item_list` ADD `item_type` varchar(20) NOT NULL DEFAULT 'found'");
		}
		$data = "";
		$err = "";
		foreach($_POST as $k =>$v){
			if(!is_array($_POST[$k]) && !in_array($k,array('id', 'founder'))){
				if(!empty($data)) $data .=",";
				$v = htmlspecialchars($this->conn->real_escape_string($v));
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `item_list` set {$data} ";
		}else{
			$sql = "UPDATE `item_list` set {$data} where id = '{$id}' ";
		}
			$save = $this->conn->query($sql);
		if($save){
			$resp['status'] = 'success';
			$iid = empty($id) ? $this->conn->insert_id : $id;
			$resp['iid'] = $iid;
			$data = "";
			if(!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])){
				if(!is_dir(base_app."uploads/items"))
					mkdir(base_app."uploads/items", 0777, true);
				$tmp_name = $_FILES['image']['tmp_name'];
				$img_info = @getimagesize($tmp_name);
				$mime = $img_info['mime'] ?? ($_FILES['image']['type'] ?? '');
				$mime_aliases = array(
					'image/jpg' => 'image/jpeg',
					'image/pjpeg' => 'image/jpeg',
				);
				if(isset($mime_aliases[$mime])){
					$mime = $mime_aliases[$mime];
				}
				$allowed = array('image/jpeg', 'image/png', 'image/webp');
				if(!in_array($mime, $allowed)){
					$err = "Image file type is invalid. Only JPEG, PNG and WEBP are accepted.";
				}else{
					$base_name = "uploads/items/$iid";
					$processed = false;
					$output_ext = 'png';
					$uploadfile = false;
					if($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')){
						$uploadfile = @imagecreatefromjpeg($tmp_name);
					}elseif($mime === 'image/png' && function_exists('imagecreatefrompng')){
						$uploadfile = @imagecreatefrompng($tmp_name);
					}elseif($mime === 'image/webp' && function_exists('imagecreatefromwebp')){
						$uploadfile = @imagecreatefromwebp($tmp_name);
					}

					if($uploadfile){
						list($width,$height) = $img_info ?: array(imagesx($uploadfile), imagesy($uploadfile));
						$temp = @imagescale($uploadfile, $width, $height);
						if($temp && @imagepng($temp, base_app.$base_name.'.png')){
							$processed = true;
							$image_path = $base_name.'.png';
							$this->conn->query("UPDATE `item_list` set `image_path` = CONCAT('{$image_path}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$iid}'");
						}
						if(isset($temp) && $temp){ imagedestroy($temp); }
						if(isset($uploadfile) && $uploadfile){ imagedestroy($uploadfile); }
					}

					if(!$processed){
						$source_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
						$ext_map = array(
							'jpg' => 'jpg',
							'jpeg' => 'jpg',
							'png' => 'png',
							'webp' => 'webp',
						);
						if(!isset($ext_map[$source_ext])){
							$source_ext = $mime === 'image/jpeg' ? 'jpg' : ($mime === 'image/png' ? 'png' : 'webp');
						}else{
							$source_ext = $ext_map[$source_ext];
						}
						$image_path = $base_name.'.'.$source_ext;
						if(@move_uploaded_file($tmp_name, base_app.$image_path)){
							$this->conn->query("UPDATE `item_list` set `image_path` = CONCAT('{$image_path}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$iid}'");
						}else{
							$err = "Image could not be processed or saved.";
						}
					}
				}
			}elseif(!empty($_FILES['image']['name']) && isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE){
				$upload_errors = array(
					UPLOAD_ERR_INI_SIZE => 'The selected image exceeds the server upload limit.',
					UPLOAD_ERR_FORM_SIZE => 'The selected image is too large for this form.',
					UPLOAD_ERR_PARTIAL => 'The image upload was interrupted. Please try again.',
					UPLOAD_ERR_NO_TMP_DIR => 'Upload failed because the temporary folder is missing.',
					UPLOAD_ERR_CANT_WRITE => 'Upload failed because the server could not save the file.',
					UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
				);
				$err = $upload_errors[$_FILES['image']['error']] ?? 'Image upload failed.';
			}
			if(!empty($err)){
				$resp['status'] = 'failed';
				$resp['msg'] = $err;
			}else{
				if(empty($id)){
					if(!isset($founder))
						$resp['msg'] = "New Item Data has been saved successfully.";
						else
						$resp['msg'] = "Found Item Data successfully submitted. We'll review your submitted details first before publishing it to the public.";
				}else
					$resp['msg'] = " Item Data has been updated successfully.";
			}
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "Database error: " . $this->conn->error;
		}
		if($resp['status'] == 'success')
			$this->settings->set_flashdata('success',$resp['msg']);
			return json_encode($resp);
	}
	function delete_item(){
		extract($_POST);
		if(empty($id)){
			$resp['status'] = 'failed';
			$resp['msg'] = "Item ID is required";
			return json_encode($resp);
		}
		// Delete item image if it exists
		$item_qry = $this->conn->query("SELECT `image_path` FROM `item_list` where id = '{$id}'");
		if($item_qry && $item_qry->num_rows > 0){
			$item = $item_qry->fetch_assoc();
			if(!empty($item['image_path'])){
				$img_parts = explode('?', $item['image_path']);
				$img_path = base_app.$img_parts[0];
				if(is_file($img_path)){
					@unlink($img_path);
				}
			}
		}
		$del = $this->conn->query("DELETE FROM `item_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$resp['msg'] = " Item Data successfully deleted.";
			$this->settings->set_flashdata('success'," Item Data successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "Failed to delete item: ".$this->conn->error;
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_page(){
		extract($_POST);
		if(!is_dir(base_app.'pages'))
		mkdir(base_app.'pages');
		if(isset($page['welcome'])){
			$content = $page['welcome'];
			$save = file_put_contents(base_app.'pages/welcome.html', $content);
		}
		if(isset($page['about'])){
			$content = $page['about'];
			$save = file_put_contents(base_app.'pages/about.html', $content);
		}
		$this->settings->set_flashdata('success', "Page Content has been updated successfully");
		return json_encode(['status' => 'success']);
	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'delete_img':
		echo $Master->delete_img();
	break;
	case 'save_category':
		echo $Master->save_category();
	break;
	case 'delete_category':
		echo $Master->delete_category();
	break;
	case 'save_page':
		echo $Master->save_page();
	break;
	case 'save_item':
		echo $Master->save_item();
	break;
	case 'delete_item':
		echo $Master->delete_item();
	break;
	case 'approve_item':
		echo $Master->approve_item();
	break;
	case 'save_inquiry':
		echo $Master->save_inquiry();
	break;
	case 'delete_inquiry':
		echo $Master->delete_inquiry();
	break;
	case 'request_claim':
		echo $Master->request_claim();
	break;
	case 'approve_claim':
		echo $Master->approve_claim();
	break;
	default:
		// echo $sysset->index();
		break;
}