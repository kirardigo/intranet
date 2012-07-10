<?php
	class ImagesController extends AppController
	{
		var $createPath = '/tmp';
		
		/**
		 * Action to generate a thumbnail of an image from DocPop.
		 * @param int $imageID The ID of the image to get.
		 */
		function thumbnail($imageID)
		{
			$user = $this->Session->read('user');
			
			$this->Image->id = $imageID;
			$blob = $this->Image->field('Thumbnail');
			
			$filename = $this->createPath . DS . $imageID . rand() . $user;
			$input = $filename . '.temp';
			$output = $filename . '.jpg';
			
			file_put_contents($input, $blob);
			exec('convert ' . $input . ' ' . $output);
			unlink($input);
			
			header('Content-Type: image/jpeg');
			echo file_get_contents($output);
			unlink($output);
			
			die();
		}
		
		/**
		 * Action to get a full size image from DocPop.
		 * @param int $imageID The ID of the image to get.
		 */
		function get($imageID)
		{
			$user = $this->Session->read('user');
			
			$this->Image->id = $imageID;
			$blob = $this->Image->field('Image');
			
			$filename = $this->createPath . DS . $imageID . rand() . $user;
			$input = $filename . '.temp';
			$output = $filename . '.jpg';
			
			file_put_contents($input, $blob);
			exec('convert ' . $input . ' ' . $output);
			unlink($input);
			
			header('Content-Type: image/jpeg');
			echo file_get_contents($output);
			unlink($output);
			
			die();
		}
	}
?>