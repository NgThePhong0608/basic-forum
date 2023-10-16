<?php 
$_SESSION['formToken']['topics'] = password_hash(uniqid(),PASSWORD_DEFAULT);
$from = $_GET['from'] ?? date("Y-m-d");
$to = $_GET['to'] ?? date("Y-m-t");

?>
<h1 class="text-center fw-bolder">List of My Created Topics</h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<div class="col-lg-10 col-md-11 col-sm-12 mx-auto py-3">
    <div class="card rounded-0 shadow">
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <div class="row justify-content-end mb-3">
                    <div class="col-auto">
                        <a class="btn btn-sm btn-primary rounded-0 d-flex align-items-center" href="./?page=manage_topic"><i class="material-symbols-outlined">add</i> Add New</a>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="row align-items-end">
                        <div class="col-lg-4 col-md-5 col-sm-12 col-12">
                            <label for="date_from">Date From</label>
                            <input type="date" value="<?= $from ?>" class="form-control rounded-0" id="date_from" name="date_from" required="required">
                        </div>
                        <div class="col-lg-4 col-md-5 col-sm-12 col-12">
                            <label for="date_to">Date To</label>
                            <input type="date" value="<?= $to ?>" class="form-control rounded-0" id="date_to" name="date_to" required="required">
                        </div>
                        <div class="col-lg-4 col-md-5 col-sm-12 col-12">
                            <button class="btn btn-primary rounded-0 d-flex align-items-center" id="filter"><span class="material-symbols-outlined">filter_alt</span> Filter</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover table-striped">
                        <colgroup>
                            <col width="5%">
                            <col width="20%">
                            <col width="35%">
                            <col width="10%">
                            <col width="15%">
                            <col width="15%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Date Added</th>
                                <th class="text-center">Title</th>
                                <th class="text-center">Total Comments</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $from = new DateTime($from, new DateTimeZone('Asia/Manila'));
                            $from->setTimezone(new DateTimeZone('UTC'));
                            $from = $from->format("Y-m-d");
                            $to = new DateTime($to, new DateTimeZone('Asia/Manila'));
                            $to->setTimezone(new DateTimeZone('UTC'));
                            $to = $to->format("Y-m-d");
                            $i = 1;
                            $topics_sql = "SELECT * FROM `topic_list` where `user_id` = '{$_SESSION['user_id']}' and date(`date_created`) BETWEEN '{$from}' and '{$to}' ORDER BY strftime('%s', `date_created`) desc";

                            $topics_qry = $conn->query($topics_sql);
                            while($row = $topics_qry->fetchArray()):
                                $date_created = new DateTime($row['date_created'], new DateTimeZone('UTC'));$date_created->setTimezone(new DateTimeZone('Asia/Manila'));
                                
                                $totalComments = $conn->querySingle("SELECT COUNT(`comment_id`) FROM `comment_list` where `topic_id` = '{$row['topic_id']}'");
                            ?>
                            <tr>
                                <td class="text-center"><?= $i++; ?></td>
                                <td><?= $date_created->format('Y-m-d g:i A') ?></td>
                                <td><?= $row['title'] ?></td>
                                <td class="text-center"><?= number_format($totalComments) ?></td>
                                <td class="text-center">
                                    <?php 
                                        switch($row['status']){
                                            case 0:
                                                echo "<span class='badge bg-light border rounded-pill px-3 text-dark'>Unpublished</span>";
                                                break;
                                            case 1:
                                                echo "<span class='badge bg-primary border rounded-pill px-3'>Published</span>";
                                                break;
                                            default:
                                                echo "<span class='badge bg-white border rounded-pill px-3'>N/A</span>";
                                                break;
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-sm btn-outline-dark rounded-0 view_data" href="./?page=view_topic&id=<?= $row['topic_id'] ?>" data-id='<?= $row['topic_id'] ?>' title="View Topic"><span class="material-symbols-outlined">subject</span></a>
                                        <a class="btn btn-sm btn-outline-primary rounded-0 edit_data" href="./?page=manage_topic&id=<?= $row['topic_id'] ?>" data-id='<?= $row['topic_id'] ?>' title="Edit Topic"><span class="material-symbols-outlined">edit</span></a>
                                        <button class="btn btn-sm btn-outline-danger rounded-0 delete_data" type="button" data-id='<?= $row['topic_id'] ?>' title="Delete Topic"><span class="material-symbols-outlined">delete</span></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(!$topics_qry->fetchArray()): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No data found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
        $('#filter').click(function(e){
            e.preventDefault()
            var from = $('#date_from').val()
            var to = $('#date_to').val()
            location.replace(`./?page=topics&from=${from}&to=${to}`)
        })
    })
</script>
