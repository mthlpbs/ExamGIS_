<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
   header('Location: login.php');
   exit();
}

if(isset($_POST['delete_comment'])){

   $delete_id = $_POST['comment_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ?");
   $verify_comment->execute([$delete_id]);

   if($verify_comment->rowCount() > 0){
      $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
      $delete_comment->execute([$delete_id]);
      $message[] = 'comment deleted successfully!';
   }else{
      $message[] = 'comment already deleted!';
   }

}

if(isset($_POST['update_now'])){

   $update_id = $_POST['update_id'];
   $update_id = filter_var($update_id, FILTER_SANITIZE_STRING);
   $update_box = $_POST['update_box'];
   $update_box = filter_var($update_box, FILTER_SANITIZE_STRING);

   $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? AND comment = ? ORDER BY date DESC");
   $verify_comment->execute([$update_id, $update_box]);

   if($verify_comment->rowCount() > 0){
      $message[] = 'comment already added!';
   }else{
      $update_comment = $conn->prepare("UPDATE `comments` SET comment = ? WHERE id = ?");
      $update_comment->execute([$update_box, $update_id]);
      $message[] = 'comment edited successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <!-- primary meta data-->
   <meta http-equiv="Content-Type" charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="keywords" content="Login,Log in,Signin,Sign in" lang="en">
   <meta name="title" content="ExamGIS: Your Ultimate Study Companion">
   <meta name="description" content=" ExamGIS is a user-friendly platform that provides essential study materials for Saegis students. Whether you need resources, textbooks, or past papers, ExamGIS has you covered. It’s designed to support your academic journey and help you excel in your studies">
   <meta name="language" content="English">
   <meta name="author" content="TCM inc">
   <meta name="owner" content="-">

   <!-- meta properties -->
   <title>User Comments | ExamGIS</title>
   <meta name="title" content="ExamGIS: Your Ultimate Study Companion" />
   <meta name="description" content="ExamGIS is a comprehensive platform designed to support students at Saegis Campus. Whether you’re looking for resources, textbooks, or past papers, ExamGIS has you covered. Our user-friendly interface provides easy access to essential study materials, helping you excel in your academic journey." />
   <meta property="og:type" content="website" />
   <meta property="og:url" content="https://examgis.rf.gd" />
   <meta property="og:title" content="ExamGIS: Your Ultimate Study Companion" />
   <meta property="og:description" content="ExamGIS is a comprehensive platform designed to support students at Saegis Campus. Whether you’re looking for resources, textbooks, or past papers, ExamGIS has you covered. Our user-friendly interface provides easy access to essential study materials, helping you excel in your academic journey." />
   <meta property="og:image" content="https://i.imgur.com/WVc46oI.jpeg" />
   <meta property="twitter:card" content="summary_large_image" />
   <meta property="twitter:url" content="https://examgis.rf.gd" />
   <meta property="twitter:title" content="ExamGIS: Your Ultimate Study Companion" />
   <meta property="twitter:description" content="ExamGIS is a comprehensive platform designed to support students at Saegis Campus. Whether you’re looking for resources, textbooks, or past papers, ExamGIS has you covered. Our user-friendly interface provides easy access to essential study materials, helping you excel in your academic journey." />
   <meta property="twitter:image" content="https://i.imgur.com/WVc46oI.jpeg" />

   <!-- Fav-icon -->
   <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon/apple-touch-icon.png">
   <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon/favicon-32x32.png">
   <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon/favicon-16x16.png">
   <link rel="manifest" href="/site.webmanifest">

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<?php
   if(isset($_POST['edit_comment'])){
      $edit_id = $_POST['comment_id'];
      $edit_id = filter_var($edit_id, FILTER_SANITIZE_STRING);
      $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? LIMIT 1");
      $verify_comment->execute([$edit_id]);
      if($verify_comment->rowCount() > 0){
         $fetch_edit_comment = $verify_comment->fetch(PDO::FETCH_ASSOC);
?>
<section class="edit-comment">
   <h1 class="heading">edit comment</h1>
   <form action="" method="post">
      <input type="hidden" name="update_id" value="<?= $fetch_edit_comment['id']; ?>">
      <textarea name="update_box" class="box" maxlength="1000" required placeholder="please enter your comment" cols="30" rows="10"><?= $fetch_edit_comment['comment']; ?></textarea>
      <div class="flex">
         <a href="comments.php" class="inline-option-btn">cancel edit</a>
         <input type="submit" value="update now" name="update_now" class="inline-btn">
      </div>
   </form>
</section>
<?php
   }else{
      $message[] = 'comment was not found!';
   }
}
?>

<section class="comments">

   <h1 class="heading">your comments</h1>

   
   <div class="show-comments">
      <?php
         $select_comments = $conn->prepare("SELECT * FROM `comments` WHERE user_id = ?");
         $select_comments->execute([$user_id]);
         if($select_comments->rowCount() > 0){
            while($fetch_comment = $select_comments->fetch(PDO::FETCH_ASSOC)){
               $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ?");
               $select_content->execute([$fetch_comment['content_id']]);
               $fetch_content = $select_content->fetch(PDO::FETCH_ASSOC);
      ?>
      <div class="box" style="<?php if($fetch_comment['user_id'] == $user_id){echo 'order:-1;';} ?>">
         <div class="content"><span><?= $fetch_comment['date']; ?></span><p> - <?= $fetch_content['title']; ?> - </p><a href="view_pdf.php?get_id=<?= $fetch_content['id']; ?>">view content</a></div>
         <p class="text"><?= $fetch_comment['comment']; ?></p>
         <?php
            if($fetch_comment['user_id'] == $user_id){ 
         ?>
         <form action="" method="post" class="flex-btn">
            <input type="hidden" name="comment_id" value="<?= $fetch_comment['id']; ?>">
            <button type="submit" name="edit_comment" class="inline-option-btn">edit comment</button>
            <button type="submit" name="delete_comment" class="inline-delete-btn" onclick="return confirm('delete this comment?');">delete comment</button>
         </form>
         <?php
         }
         ?>
      </div>
      <?php
       }
      }else{
         echo '<p class="empty">no comments added yet!</p>';
      }
      ?>
      </div>
   
</section>

<!-- comments section ends -->












<!-- custom js file link  -->
<script src="js/script.js"></script>
   
</body>
</html>