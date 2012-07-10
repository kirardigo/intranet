<?php
	class BruceController extends AppController
	{
		var $components = array('DefaultFile');
		var $uses = array('Setting', 'ApplicationFolder', 'RoleApplication', 'RoleApplicationFolder', 'Customer', 'Carrier', 'CustomerCarrier', 'Invoice', 'TransactionJournal', 'TransactionQueue', 'Transaction', 'Rental');
		var $autoRender = false;
		
		function beforeRender()
		{
			if (Configure::read('debug') == 0)
			{
				die();
			}
		}
		
		function lock()
		{
		
			$file = '/tmp/test.dat';
			
			$f = dio_open($file, O_RDWR);
			
			$result = dio_fcntl($f, F_SETLKW, array(
				'type' => F_WRLCK,
				'whence' => SEEK_SET, 
				'start' => (int)1, 
				'length' => (int)1
			));
			
			pr('lock acquired: ' . date('i:s'));
			dio_open($file, O_RDWR);
			
			sleep(5);
		} 

		function lockbug()
		{
		
			$file = '/tmp/test.dat';
			
			$f = dio_open($file, O_RDWR);
			
			$result1 = dio_fcntl($f, F_SETLKW, array(
				'type' => F_WRLCK,
				'whence' => SEEK_SET, 
				'start' => (int)1, 
				'length' => (int)1
			));
			
			pr('result1: ' . $result1);

			$result2 = dio_fcntl($f, F_SETLKW, array(
				'type' => F_WRLCK,
				'whence' => SEEK_SET, 
				'start' => (int)3, 
				'length' => (int)1
			));

			pr('result2: ' . $result2);
			
			pr('lock acquired: ' . date('i:s'));
		
			$f2 = dio_open($file, O_WRONLY | O_APPEND);
			dio_write($f2, "BRUCE-WAS-HERE-AGAIN");
		
			sleep(10);
			
			pr('lockbug: end of function');
		} 

		function checklockbug()
		{
		
			$file = '/tmp/test.dat';
			
			$f = dio_open($file, O_RDWR);

			$result1 = dio_fcntl($f, F_SETLK, array(
				'type' => F_WRLCK,
				'whence' => SEEK_SET, 
				'start' => (int)1, 
				'length' => (int)1
			));
			
			pr('result1: ' . $result1);

			$result2 = dio_fcntl($f, F_SETLK, array(
				'type' => F_WRLCK,
				'whence' => SEEK_SET, 
				'start' => (int)3, 
				'length' => (int)1
			));

			pr('result2: ' . $result2);
		
			pr('checklockbug: end of function');
			
		} 

		function lockopenbug()
		{
			$file = '/tmp/test.dat';
			
			$f = dio_open($file, O_RDWR);
			
			$result1 = dio_fcntl($f, F_SETLKW, array(
				'type' => F_WRLCK,
				'whence' => SEEK_SET, 
				'start' => (int)1, 
				'length' => (int)1
			));
			
			pr('result1: ' . $result1);

			//dio_open($file, O_RDWR);
			//$f = dio_open($file, O_RDWR);
			//$f2 = dio_open($file, O_RDWR);

			sleep(10);
		
			pr('lock acquired: ' . date('i:s'));
					
			pr('lockopenbug: end of function');
		} 
		
		function auth()
		{
			pr($_SERVER);
			echo phpinfo();
		}
		
		
		function dio()
		{
			//$f = fopen('/tmp/boo2', 'r');
			$f = dio_open('/tmp/boo2', O_RDONLY);
			
//			pr(dio_stat($f, 447));
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			pr(dio_read($f, 50) == null ? 'NULL' : 'NOT NULL');
			
			//ftell
			pr(dio_seek($f, 0, SEEK_CUR));
			
			dio_close($f);
			//fclose($f);
		}

	}
?>