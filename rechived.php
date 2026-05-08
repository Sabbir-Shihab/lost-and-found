<h1 class="pageTitle text-center titleTxt">Rechived Items</h1>
<hr class="mx-auto bg-primary border-primary opacity-100" style="width:50px">

<div class="container-sm">
    <div class="row">
        <div class="col-12">
            <div class="card rounded-0">
                <div class="card-body">
                    <?php
                    $items = $conn->query("SELECT i.*, cr.name as received_by, cr.contact as received_contact, cr.approved_at 
                        FROM `item_list` i 
                        LEFT JOIN `claim_requests` cr ON cr.item_id = i.id AND cr.status = 1
                        WHERE i.status = 2 
                        ORDER BY cr.approved_at DESC, i.created_at DESC")->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <div id="received-list">
                        <?php if(count($items) > 0): ?>
                            <div class="row g-3">
                                <?php foreach($items as $row): ?>
                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                        <div class="card h-100">
                                            <div class="item-card-img">
                                                <img src="<?= validate_image($row['image_path']) ?>" alt="">
                                            </div>
                                            <div class="card-body pt-3">
                                                <h4 class="card-title"><?= $row['title'] ?></h4>
                                                <span class="badge bg-success">Owner Found</span>
                                                <small class="text-muted d-block mt-2">Received on <?= isset($row['approved_at']) ? date("F d, Y g:i A", strtotime($row['approved_at'])) : 'N/A' ?></small>
                                                <p class="mt-2 mb-1"><strong>Received by:</strong> <?= htmlspecialchars($row['received_by'] ?? 'N/A') ?></p>
                                                <p class="mb-1"><strong>Contact:</strong> <?= htmlspecialchars($row['received_contact'] ?? 'N/A') ?></p>
                                                <p class="truncate-3"><?= strip_tags(htmlspecialchars_decode($row['description'])) ?></p>
                                                <a href="<?= base_url.'?page=items/view&id='.$row['id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted text-center">No Rechived items yet</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
