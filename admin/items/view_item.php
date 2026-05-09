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
					<?php if(isset($item_type)): ?>
						<?php if($item_type == 'found'): ?>
							<span class="badge bg-primary">Found</span>
						<?php else: ?>
							<span class="badge bg-warning text-dark">Missing</span>
						<?php endif; ?>
					<?php endif; ?>
					<p class="text-muted mb-3">Uploaded on <?= isset($created_at) ? date("F d, Y g:i A", strtotime($created_at)) : "N/A" ?></p>
                    <dl>
						<dt class="text-muted"><?php echo (isset($item_type) && $item_type == 'missing') ? 'Owner Name' : 'Founder Name'; ?></dt>
						<dd class="ps-4"><?= $fullname ?? "" ?></dd>
						<dt class="text-muted">Contact No.</dt>
						<dd class="ps-4"><?= $contact ?? "" ?></dd>
						<dt class="text-muted">Description</dt>
						<dd class="ps-4"><?= isset($description) ? str_replace("\n", "<br>", ($description)) : "" ?></dd>
                      <dt class="text-muted">Status</dt>
                        <dd class="ps-4">
                            <?php if($status == 1): ?>
                                <span class="badge bg-primary px-3 rounded-pill">Published</span>
                            <?php elseif($status == 2): ?>
                                <span class="badge bg-success px-3 rounded-pill">Claimed</span>
                            <?php else: ?>
                                <span class="badge bg-secondary px-3 rounded-pill">Pending</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
						<?php
							// show pending claim requests for this item
							$claim_q = $conn->query("SELECT * FROM `claim_requests` WHERE item_id = '{$id}' AND status = 0");
							if($claim_q && $claim_q->num_rows > 0):
						?>
						<hr />
						<h5>Pending Claim Requests</h5>
						<div class="list-group">
							<?php while($c = $claim_q->fetch_assoc()): ?>
								<div class="list-group-item">
									<div><strong><?= htmlspecialchars($c['name']) ?></strong> - <?= htmlspecialchars($c['contact']) ?></div>
									<div class="small text-muted mb-2"><?= htmlspecialchars($c['message']) ?></div>
									<button class="btn btn-sm btn-success approve-claim" data-id="<?= $c['id'] ?>">Approve</button>
								</div>
							<?php endwhile; ?>
						</div>
						<?php endif; ?>
                </div>
            </div>
			<div class="card-footer py-1 text-center">
				<?php if($status == 0): ?>
					<button class="btn btn-success btn-sm bg-gradient-success rounded-0" type="button" id="approve_data"><i class="fa fa-check"></i> Approve</button>
				<?php endif; ?>
				<button class="btn btn-danger btn-sm bg-gradient-danger rounded-0" type="button" id="delete_data"><i class="fa fa-trash"></i> Delete</button>
				<a class="btn btn-primary btn-sm bg-gradient-teal rounded-0" href="./?page=items/manage_item&id=<?= isset($id) ? $id : '' ?>"><i class="fa fa-edit"></i> Edit</a>
				<a class="btn btn-light btn-sm bg-gradient-light border rounded-0" href="./?page=items"><i class="fa fa-angle-left"></i> Back to List</a>
			</div>
		</div>
	</div>
</div>
<script>
	$(function(){
		$(document).on('click','#delete_data',function(){
			_conf("Are you sure to delete this item permanently?","delete_item", ["<?= isset($id) ? $id :'' ?>"])
		})
		$(document).on('click','#approve_data',function(){
			_conf("Approve this item and make it visible to users?","approve_item", ["<?= isset($id) ? $id :'' ?>"])
		})
		$(document).on('click','.approve-claim',function(){
			var cid = $(this).data('id');
			_conf("Approve this claim and mark item as claimed?","approve_claim", [cid])
		})
	})

	function delete_item($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_item",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occurred while deleting item.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					alert_toast("Item deleted successfully.",'success');
					setTimeout(()=>{location.replace("./?page=items");},1000);
				}else{
					var msg = (resp.msg) ? resp.msg : "Failed to delete item";
					alert_toast(msg,'error');
					end_loader();
				}
			}
		})
	}

	function approve_item($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=approve_item",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occurred while approving item.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					alert_toast("Item approved successfully.",'success');
					setTimeout(()=>{location.replace("./?page=items");},1000);
				}else{
					var msg = (resp.msg) ? resp.msg : "Failed to approve item";
					alert_toast(msg,'error');
					end_loader();
				}
			}
		})
	}

	function approve_claim($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=approve_claim",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occurred while approving claim.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					alert_toast(resp.msg,'success');
					setTimeout(()=>{location.replace("./?page=rechived");},1000);
				}else{
					var msg = (resp.msg) ? resp.msg : "Failed to approve claim";
					alert_toast(msg,'error');
					end_loader();
				}
			}
		})
	}
</script>