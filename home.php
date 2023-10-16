<h1 class="text-center fw-bolder">Welcome to Simple Forum Site</h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<?php if($_SESSION['type'] != 1): ?>
    <div class="col-lg-8 col-md-10 col-sm-12 col-12 mx-auto">
        <?php
            $topics_sql = "SELECT *, COALESCE((SELECT `fullname` FROM `user_list` where `user_list`.`user_id` = `topic_list`.`user_id`),'N/A') as `author` FROM `topic_list` where `status` = 1 ORDER BY strftime('%s', `date_created`) desc";

            $topics_qry = $conn->query($topics_sql);
            while($row = $topics_qry->fetchArray()):
                $date_created = new DateTime($row['date_created'], new DateTimeZone('UTC'));$date_created->setTimezone(new DateTimeZone('Asia/Manila'));
                
                $totalComments = $conn->querySingle("SELECT COUNT(`comment_id`) FROM `comment_list` where `topic_id` = '{$row['topic_id']}'");
        ?>
        <a class="card rounded-0 shadow mb-3 text-reset text-decoration-none topic-item" href="./?page=view_topic&id=<?= $row['topic_id'] ?>&fromFeed=true">
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <h3 class="fw-bolder"><?= $row['title'] ?></h3>
                    <hr>
                    <div class="lh-1 d-flex w-100 justify-content-between mb-3">
                        <div><small class="text-secondary">Author: <?= $row['author'] ?></small></div>
                        <div><small class="text-secondary">Created At: <?= $date_created->format("M d, Y g:i A") ?></small></div>
                    </div>
                    <p class="truncate-3"><?= $row['description'] ?></p>
                </div>
            </div>
        </a>
        <?php endwhile; ?>

    </div>
<?php endif; ?>