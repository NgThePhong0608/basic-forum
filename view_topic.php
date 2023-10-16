<?php 
if(isset($_GET['id']) && $_GET['id'] > 0){
   
    $sql = "SELECT *, COALESCE((SELECT `fullname` FROM `user_list` where `user_list`.`user_id` = `topic_list`.`user_id`),'N/A') as `author` FROM `topic_list` where `topic_id` = '{$_GET['id']}' ";
    $query = $conn->query($sql);
    $data = $query->fetchArray();
    $date_created = new DateTime($data['date_created'], new DateTimeZone('UTC'));
    $date_created->setTimezone(new DateTimeZone('Asia/Manila'));
    $created_date = $date_created->format("M d, Y g:i A");
    if($data['status'] == 0 && $_SESSION['user_id'] != $data['user_id']){
        throw new ErrorException("Invalid ID or you don't have permission to access this page.");
        exit;
    }

}else{
    throw new ErrorException("This page requires a valid ID.");
}
$_SESSION['formToken']['topics'] = password_hash(uniqid(), PASSWORD_DEFAULT);
$_SESSION['formToken']['topicDetails'] = password_hash(uniqid(), PASSWORD_DEFAULT);
$_SESSION['formToken']['comment-form'] = password_hash(uniqid(), PASSWORD_DEFAULT);
?>
<h1 class="text-center fw-bolder">Topic Details</h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<div class="col-lg-8 col-md-10 col-sm-12 mx-auto py-3">
    <div class="card rounded-0 shadow">
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <div class="d-flex align-items-start">
                    <div class="col-auto flex-shrink-1 flex-grow-1">
                        <div class="lh-1">
                            <h2><b><?= $data['title'] ?? "" ?></b></h2>
                            <div><small class="text-secondary">Author: <?= $data['author'] ?></small></div>
                            <div><small class="text-muted">Posted: <?= $created_date ?></small></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <?php if(isset($data['status'])):
                            switch($data['status']){
                                case 0:
                                    echo "<span class='badge bg-light border rounded-pill px-3 text-dark'>Unpublished</span>";
                                    break;
                                case 1:
                                    echo "<span class='badge bg-primary border rounded-pill px-3'>Published</span>";
                                    break;
                                default:
                                    echo "<span class='badge bg-danger border rounded-pill px-3'>Closed</span>";
                                    break;
                            }
                        endif;
                        ?>
                    </div>
                </div>
                <hr>
                <?php if(isset($data['description'])): ?>
                <p><?= str_replace(["\n"], "<br/>", str_replace(["\n\r"], "<br/>", $data['description'])) ?></p>
                <?php endif; ?>
                <?php if(isset($data['topic_id'])): ?>
                <hr>
                <h4 class="fw-bold">Comment(s):</h4>
                <?php 
                $comment_sql = $conn->query("SELECT *, COALESCE((SELECT `fullname` FROM `user_list` where `user_list`.`user_id` = `comment_list`.`user_id`),'N/A') as `author` FROM `comment_list` where `topic_id` = '{$data['topic_id']}' ORDER BY strftime('%s', `date_created`) desc");
                while($row = $comment_sql->fetchArray(SQLITE3_ASSOC)):
                    $date_created = new DateTime($row['date_created'], new DateTimeZone('UTC'));
                    $date_created->setTimezone(new DateTimeZone('Asia/Manila'));
                ?>
                <div class="card rounded-0 shadow mb-3">
                    <div class="card-header rounded-0">
                        <div class="card-title">
                            <div class="d-flex w-100 justify-content-between">
                                <div class="col-auto flex-grow-1"><b><?= $row['author'] ?></b></div>
                                <div class="col-auto"><b><?= $date_created->format("M d, Y g:i A") ?></b></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body rounded-0">
                        <div class="container-fluid">
                            <p><?= str_replace(["\n"], "<br/>", str_replace(["\n\r"], "<br/>", $row['comment'])) ?></p>
                        </div>
                    </div>
                    <?php if($_SESSION['user_id'] == $row['user_id']): ?>
                        <div class="card-footer text-end">
                            <button class="btn btn-sm btn-primary rounded-0 edit_comment" type="button" data-id="<?= $row['comment_id'] ?>">Edit</button>
                            <button class="btn btn-sm btn-danger rounded-0 delete_comment" type="button" data-id="<?= $row['comment_id'] ?>">Delete</button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
                <?php if(!$comment_sql->fetchArray()): ?>
                    <div class="text-center text-secondary fst-italic">No comment listed yet.</div>
                <?php endif; ?>
                <form action="" id="comment-form">
                    <input type="hidden" name="formToken" value="<?= $_SESSION['formToken']['comment-form'] ?>">
                    <input type="hidden" name="topic_id" value="<?= $data['topic_id'] ?>">
                    <div class="mb-3">
                        <label for="comment" class="form-label fw-bolder">Leave a Comment</label>
                        <textarea name="comment" id="comment" cols="30" rows="5" class="form-control rounded-0" required="required" placeholder="Write your comment here..."></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex w-100 justify-content-end">
                            <button class="btn btn-primary rounded-0">Post Comment</button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
                <hr>
                <div class="text-center">
                    <?php if(isset($_GET['fromFeed'])): ?>
                        <a href="./" class="btn btn-sm btn-secondary rounded-0">Back</a>
                    <?php else: ?>
                        <a href="./?page=topic" class="btn btn-sm btn-secondary rounded-0">Back to List</a>
                    <?php endif; ?>
                    <?php if(isset($data['topic_id']) && $data['user_id'] == $_SESSION['user_id']): ?>
                        <a href="./?page=manage_topic&id=<?= $data['topic_id'] ?>&toview=true" class="btn btn-sm btn-primary rounded-0">Edit</a>
                        <button type="button" data-id="<?= $data['topic_id'] ?>&toview=true" class="btn btn-sm btn-danger rounded-0 delete_data">Delete</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="EditCommentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable rounded-0 modal-lg modal-fullscreen-md-down">
        <div class="modal-content">
        <div class="modal-header rounded-0">
            <h5 class="modal-title">Edit Comment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body rounded-0">
            <div class="container-fluid">
                <form action="" id="update-comment-form">
                    <input type="hidden" name="formToken" value="<?= $_SESSION['formToken']['comment-form'] ?>">
                    <input type="hidden" name="comment_id" value="">
                    <div class="mb-3">
                        <label for="comment">Comment</label>
                        <textarea rows="5" class="form-control rounded-0" id="comment" name="comment" required="required"></textarea>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary rounded-0" form="update-comment-form">Save</button>
            <button type="button" class="btn btn-secondary rounded-0" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('.delete_data').on('click', function(e){
            e.preventDefault()
            var id = $(this).attr('data-id');
            start_loader()
            var _conf = confirm(`Are you sure to delete this topic data? This action cannot be undone`);
            if(_conf === true){
                $.ajax({
                    url:'Master.php?a=delete_topic',
                    method:'POST',
                    data: {
                        token: '<?= $_SESSION['formToken']['topics'] ?>',
                        id: id
                    },
                    dataType:'json',
                    error: err=>{
                        console.error(err)
                        alert("An error occurred.")
                        end_loader()
                    },
                    success:function(resp){
                        if(resp.status == 'success'){
                            location.replace("./?page=topic")
                        }else{
                            console.error(resp)
                            alert(resp.msg)
                        }
                        end_loader()
                    }
                })
            }else{
                end_loader()
            }
        })
        $('.edit_comment').click(function(e){
            e.preventDefault();
            var comment_id = $(this).attr('data-id')
            start_loader()
            $.ajax({
                url: "Master.php?a=get_comment",
                method: "POST",
                data: {comment_id:comment_id, formToken: '<?= $_SESSION['formToken']['topicDetails'] ?>'},
                dataType:"JSON",
                error: err=>{
                    alert("An error occurred while fetching the data.")
                    end_loader()
                    console.error(err)
                },
                success: function(resp){
                    console.log(typeof resp)
                    if(typeof resp === "object"){
                        var modal = $('#EditCommentModal')
                        modal.find('[name="comment_id"]').val(resp.comment_id)
                        modal.find('[name="comment"]').val(resp.comment)
                        modal.modal('show')
                    }else{
                        alert("An error occurred while fetching the data.")
                        console.error(resp)
                    }
                    end_loader()
                }
            })
        })
        $('.delete_comment').on('click', function(e){
            e.preventDefault()
            var comment_id = $(this).attr('data-id');
            start_loader()
            var _conf = confirm(`Are you sure to delete this comment? This action cannot be undone`);
            if(_conf === true){
                $.ajax({
                    url:'Master.php?a=delete_comment',
                    method:'POST',
                    data: {
                        token: '<?= $_SESSION['formToken']['topicDetails'] ?>',
                        comment_id: comment_id
                    },
                    dataType:'json',
                    error: err=>{
                        console.error(err)
                        alert("An error occurred.")
                        end_loader()
                    },
                    success:function(resp){
                        if(resp.status == 'success'){
                            location.reload()
                        }else{
                            console.error(resp)
                            alert(resp.msg)
                        }
                        end_loader()
                    }
                })
            }else{
                end_loader()
            }
        })
        $('#comment-form, #update-comment-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            $.ajax({
                url:'./Master.php?a=save_comment',
                method:'POST',
                data:$(this).serialize(),
                dataType:'JSON',
                error:err=>{
                    console.log(err)
                    _el.addClass('alert alert-danger')
                    _el.text("An error occurred.")
                    _this.prepend(_el)
                    _el.show('slow')
                    _this.find('button').attr('disabled',false)
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        location.reload();
                        return false;
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                    _this.find('button').attr('disabled',false)
                }
            })
        })
    })
</script>
