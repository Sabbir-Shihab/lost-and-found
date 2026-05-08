<?php 
if(isset($_GET['cid'])){
    $category_qry = $conn->query("SELECT * FROM `category_list` where `id` = '{$_GET['cid']}'");
    if($category_qry->num_rows > 0){
        foreach($category_qry->fetch_assoc() as $k => $v){
            $cat[$k] = $v; 
        }
    }
}
?>
<h1 class="pageTitle text-center titleTxt">Lost and Found Items</h1>
<hr class="mx-auto bg-primary border-primary opacity-100" style="width:50px">

<div class="container-sm">
    <div class="row">
        <div class="col-12">
        <?php if(isset($cat['name'])): ?>
            <h3 class="titleTxt"><?= $cat['name'] ?></h3>
        <?php endif; ?>
        <?php if(isset($cat['description'])): ?>
            <div ><?= str_replace("\n", "<br>", htmlspecialchars_decode($cat['description'])) ?></div>
        <?php endif; ?>
            <?php 
            $where = "";
            if(isset($cat['id'])){
                $where = " and `category_id` = '{$cat['id']}'";
            }
            // show only published items on the public listing
            $items = $conn->query("SELECT * FROM `item_list` where `status` = 1 {$where} order by `title` asc")->fetch_all(MYSQLI_ASSOC);
            ?>
            <div id="item-list">
                <?php if(count($items) > 0): ?>
                <?php foreach($items as $row): ?>
                <div class="item-item text-decoration-none text-reset" data-href="<?= base_url.'?page=items/view&id='.$row['id'] ?>">
                    <div class="card" style="cursor:pointer">
                        <div class="item-card-img">
                            <img src="<?= validate_image($row['image_path']) ?>" alt="">
                        </div>
                        <div class="card-body pt-3">
                            <h4 class="card-title"><?= $row['title'] ?></h4>
                            <small class="text-muted d-block mb-2">Uploaded on <?= date("F d, Y g:i A", strtotime($row['created_at'])) ?></small>
                            <?php if($row['status'] == 2): ?>
                                <span class="badge bg-success">Owner Found</span>
                            <?php endif; ?>
                            <p class="truncate-3"><?= strip_tags(htmlspecialchars_decode($row['description'])) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if(count($items) <= 0): ?>
                <div class="text-muted text-center">No item Listed Yet</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Claim Modal -->
<div class="modal fade" id="claimModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Claim Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="claim-form">
                    <input type="hidden" name="item_id" id="claim-item-id" value="">
                    <div class="mb-2">
                        <label class="form-label">Your Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact</label>
                        <input type="text" class="form-control" name="contact">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Message (optional)</label>
                        <textarea class="form-control" name="message"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit-claim">Send Claim</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
        var claimModal = new bootstrap.Modal(document.getElementById('claimModal'));
    // navigate to item view when card clicked (except when clicking interactive elements)
    $(document).on('click', '.item-item .card', function(e){
        if($(e.target).closest('.claim-btn, button, a, .btn').length > 0) return;
        var href = $(this).closest('.item-item').data('href');
        if(href) window.location.href = href;
    });
        $(document).on('click','.claim-btn',function(e){
                e.preventDefault();
                var id = $(this).data('id');
                $('#claim-item-id').val(id);
                claimModal.show();
        });
        $(document).on('click','#submit-claim',function(){
                var frm = $('#claim-form');
                if(!frm[0].checkValidity()){
                        frm[0].reportValidity();
                        return;
                }
                var data = frm.serialize();
                start_loader && start_loader();
                $.ajax({
                        url: _base_url_ + 'classes/Master.php?f=request_claim',
                        method: 'POST',
                        data: data,
                        dataType: 'json',
                        error: function(err){
                                end_loader && end_loader();
                                alert_toast && alert_toast('An error occurred.','error');
                        },
                        success: function(resp){
                                end_loader && end_loader();
                                if(resp.status == 'success'){
                                        claimModal.hide();
                                        alert_toast && alert_toast(resp.msg,'success');
                                }else{
                                        alert_toast && alert_toast(resp.msg || 'Failed to submit claim.','error');
                                }
                        }
                })
        })
})
</script>