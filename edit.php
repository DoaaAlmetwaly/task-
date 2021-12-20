<?php

require '../helpers/dbConnection.php';
require '../helpers/functions.php';

# Get Data related to id ......
$id = $_GET['id'];

$sql = "select * from blog where id = $id";
$op = mysqli_query($con, $sql);

if (mysqli_num_rows($op) == 1) {
    $data = mysqli_fetch_assoc($op);
} else {
    $_SESSION['Message'] = ['Message' => 'Access Denied'];
    header('Location: ' . url('Articales/index.php'));
}

# Fetch Role Data .....
$sql = 'select * from blog';
$blogData = mysqli_query($con, $sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CODE ......
    $title = Clean($_POST['title']);
    $content = Clean($_POST['content']);
    //$password = Clean($_POST['password']);
    $cat_id = $_POST['cat_id'];
    $date = Clean($_POST['date']);

    # Validation ......
    $errors = [];

    # Validate title
    if (!validate($title, 1)) {
        $errors['Title'] = 'Field Required';
    } elseif (!validate($title, 7)) {
        $errors['Title'] = 'Invalid String';
    }

    # Validate contnet
    if (!validate($content, 1)) {
        $errors['Content'] = 'Field Required';
    }  elseif (!validate($content, 3, 10)) {
        $errors['Content'] = 'Length Must >= 10 chs';
    }

   

    # Validate cat_id
    if (!validate($cat_id, 4)) {
        $errors['Category'] = 'Invalid Category';
    }

    

    # Validate date
    if (!validate($date, 1)) {
        $errors['date'] = 'Field Required';
    } 
    elseif (!validate($date, 8)) {
        $errors['date'] = 'Invalid date';
    }



    # Validate image
    if (!validate($_FILES['image']['name'], 1)) {
        $errors['Image'] = 'Field Required';
    } else {
        $tmpPath = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imageSize = $_FILES['image']['size'];
        $imageType = $_FILES['image']['type'];

        $exArray = explode('.', $imageName);
        $extension = end($exArray);

        $FinalName = rand() . time() . '.' . $extension;

        $allowedExtension = ['png', 'jpg'];

        if (!validate($extension, 5)) {
            $errors['Image'] = 'Error In Extension';
        }
    }

    if (count($errors) > 0) {
        $_SESSION['Message'] = $errors;
    } else {
        // db ..........

        // old Image
        $OldImage = $data['image'];

        if (validate($_FILES['image']['name'], 1)) {
            $desPath = './uploads/' . $FinalName;

            if (move_uploaded_file($tmpPath, $desPath)) {
                unlink('./uploads/' . $OldImage);
                
            }
        } else {
            $FinalName = $OldImage;
        }
        $date = strtotime($date); 
        $user_id = $_SESSION['user']['id'];
        $sql = "update blog set title = '$title' , content = '$content',image = '$FinalName' , cat_id = $cat_id , date = '$date',added_by= $user_id where id = $id";
        $op = mysqli_query($con, $sql);

        if ($op) {
            $_SESSION['Message'] = ['message' => 'Raw Updated'];

            header('Location: ' . url('Articales/index.php'));
            exit();
        } else {
            $_SESSION['Message'] = ['message' => 'Error Try Again'];
        }
    }
}

require '../layouts/header.php';
require '../layouts/nav.php';
require '../layouts/sidNav.php';
?>


<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">




            <h1 class="mt-4">Dashboard</h1>
            <ol class="breadcrumb mb-4">

                <?php 
                            
                              if(isset($_SESSION['Message'])){
                                foreach($_SESSION['Message'] as $key => $val){
                                echo '* '.$key.' : '.$val;
                                }
                                unset($_SESSION['Message']); 
                            }else{
                            
                            ?>

                <li class="breadcrumb-item active">Dashboard/Edit Role</li>

                <?php } ?>
            </ol>



            <div class="card-body">


                <div class="container">



                    <form action="edit.php?id=<?php echo $data['id']; ?>" method="post" enctype="multipart/form-data">




                        <div class="form-group">
                            <label for="exampleInputName">Title</label>
                            <input type="text" class="form-control" id="exampleInputName" name="title"
                                value="<?php echo $data['title']; ?>" aria-describedby="" placeholder="Enter title">
                        </div>


                        <div class="form-group">
                            <label for="exampleInputEmail">Content</label>
                            <input type="text" class="form-control" id="exampleInputEmail1" name="content"
                                value="<?php echo $data['content']; ?>" aria-describedby="emailHelp" placeholder="Enter content">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputPassword"> Phone</label>
                            <input type="date" class="form-control" value="<?php echo $data['date']; ?>"
                                id="exampleInputPassword1" name="date" placeholder="date">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputPassword">category</label>

                            <select class="form-control" name="cat_id">
                                <?php 
                               while($blogdetails = mysqli_fetch_assoc($blogData)){
                                ?>
                                <option value="<?php echo $blogdetails['id']; ?>" <?php if ($data['cat_id'] == $blogdetails['id']) {
                                 echo 'selected';
                               } ?>><?php echo $blogdetails['title']; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="exampleInputPassword">Image</label><br>
                            <input type="file" name="image">
                        </div>

                        <img src="./uploads/<?php echo $data['image']; ?>" height="80" width="80"><br>

                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>







                </div>
            </div>


        </div>
    </main>


    <?php
    
    require '../layouts/footer.php';
    ?>