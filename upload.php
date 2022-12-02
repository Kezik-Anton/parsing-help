<?php
session_start();

$message = ''; 
if (isset($_POST['uploadBtn']) && $_POST['uploadBtn'] == 'Upload')
{
  if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK)
  {

    $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
    $fileName = $_FILES['uploadedFile']['name'];
    $fileSize = $_FILES['uploadedFile']['size'];
    $fileType = $_FILES['uploadedFile']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    $allowedfileExtensions = array('csv');

    if (in_array($fileExtension, $allowedfileExtensions))
    {

      $uploadFileDir = './uploaded_files/';
      $dest_path = $uploadFileDir . $newFileName;

      if(move_uploaded_file($fileTmpPath, $dest_path)) 
      {
        $message = 'File '. $fileName . ' is successfully uploaded.';
        
        $handle = fopen($dest_path, 'r');
        $arr_parcer = array();
        $arr_name = array();
        
        if($handle) {
          while($string = fgetcsv($handle, 0, ';')) {
            $arr = array();
            foreach(explode("\n", $string[14]) as $char){
              $char = explode("|", $char);
              array_push($arr, $char);
              array_push($arr_name, $char[0]);
            }
            array_push($arr_parcer, array(
              'code' => $string[1],
              'charters' => $arr
            ));
          } 
          fclose($handle);
        } else {
          echo '<br>Can\'t open file.';
        }

        array_shift($arr_parcer);
        $arr_name = array_unique($arr_name);
        array_shift($arr_name);
        $new_arr = array();
        
        foreach($arr_parcer as $result) {
          $i = 0;
          foreach($arr_name as $name) {
            $new_arr[$result['code']][$i] = '';
            foreach($result['charters'] as $char) {
              if($char[0] == $name){
                $new_arr[$result['code']][$i] = $char[1];
              }
            }
            $i++;
          }
        }
      }
      else 
      {
        $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
      }
    }
    else
    {
      $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
    }
  }
  else
  {
    $message = 'There is some error in the file upload. Please check the following error.<br>';
    $message .= 'Error:' . $_FILES['uploadedFile']['error'];
  }
}
$_SESSION['message'] = $message;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Парсинг для озона</title>
  <style>
    html {
      font-family: Tahoma;
    }
  
		table {
			border-collapse: collapse;
      font-size: 12px;
		}
	
		th, td {
			border: 1px solid grey;
			padding: 2px;
      text-align: center;
		}
	</style>
</head>
<body>
<?php if($arr_parcer) : ?>
  <p><?=$message;?></p>
  
  <table>
	<thead>
    <tr>
      <th>Код</th>
      <?php foreach($arr_name as $names) : ?>
        <th><?=$names;?></th>
      <?php endforeach; ?>
  </tr>
	</thead>
	<tbody>
    <?php foreach($new_arr as $key => $res): ?>
			<tr>
        <td><?=$key;?></td>
        <?php foreach($res as $re): ?>
          <td><?=$re;?></td>
        <?php endforeach;?>
			</tr>
		<?php endforeach;?>
  </tbody>
</table>

<?php else : ?>
  <p>File data not found</p>
<?php endif; ?>
</body>
</html>