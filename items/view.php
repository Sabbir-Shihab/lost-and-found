<?php
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT *, COALESCE((SELECT `name` FROM `category_list` where `category_list`.`id` = `item_list`. `category_id` ) ,'N/A') as `category` from `item_list` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }else{
		echo '<script>alert("item ID is not valid."); location.replace("./?page=items")</script>';
	}
}else{
	echo '<script>alert("item ID is Required."); location.replace("./?page=items")</script>';
}
?>
<style>
	.lf-image{
		width:400px;
		height:300px;
		margin: 1em auto;
		background: #000;
		box-shadow: 1px 1px 10px #00000069;
	}
	.lf-image > img{
		width: 100%;
		height: 100%;
		object-fit: scale-down;
		object-position: center center;
	}
</style>
<div class="row mt-lg-n4 mt-md-n4 justify-content-center">
	<div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
		<div class="card rounded-0">
			<div class="card-body">
                <div class="container-fluid mt-4">
					<div class="lf-image">
						<img src="<?= validate_image($image_path ?? "") ?>" alt="<?= $title ?? "" ?>">
					</div>
					<h2 class="titleTxt"><?= $title ?? "" ?> <span>| <?= $category ?? "" ?></span></h2>
					<p class="text-muted mb-3">Uploaded on <?= isset($created_at) ? date("F d, Y g:i A", strtotime($created_at)) : "N/A" ?></p>
					<?php if(isset($status) && $status == 2): ?>
						<span class="badge bg-success">Owner Found</span>
						<?php
						// show approved claim info
						$claim_q = $conn->query("SELECT * FROM `claim_requests` WHERE item_id = '{$id}' AND status = 1 ORDER BY approved_at DESC LIMIT 1");
						if($claim_q && $claim_q->num_rows > 0){
							$c = $claim_q->fetch_assoc();
							?>
							<div class="mt-2">
								<strong>Received by:</strong> <?= htmlspecialchars($c['name']) ?> <br>
								<strong>Contact:</strong> <?= htmlspecialchars($c['contact']) ?> <br>
								<small class="text-muted">Rechived on <?= date("F d, Y g:i A", strtotime($c['approved_at'])) ?></small>
							</div>
							<?php
						}
						?>
					<?php elseif(isset($status) && $status == 1): ?>
						<button class="btn btn-primary btn-sm claim-now" data-id="<?= isset($id) ? $id : '' ?>">I am the Owner — I received it</button>
					<?php endif; ?>
                    <dl>
						<dt class="text-muted">Founder Name</dt>
						<dd class="ps-4"><?= $fullname ?? "" ?></dd>
						<dt class="text-muted">Contact No.</dt>
						<dd class="ps-4"><?= $contact ?? "" ?></dd>
						<dt class="text-muted">Description</dt>
						<dd class="ps-4"><?= isset($description) ? str_replace("\n", "<br>", ($description)) : "" ?></dd>
                    </dl>
                </div>
            </div>
		</div>
	</div>
</div>
<script>
</script>
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
		$(document).on('click','.claim-now',function(e){
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