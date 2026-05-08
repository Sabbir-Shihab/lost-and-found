<?php
if(!isset($conn)) return;
?>
<?php if($_settings->chk_flashdata('success')): ?>
<script>
	alert_toast("<?php echo $_settings->flashdata('success') ?>","success")
</script>
<?php endif;?>
<div class="card card-outline rounded-0 card-navy">
	<div class="card-header ">
		<h3 class="card-title">Claim Requests</h3>
		<div class="card-tools d-flex justify-content-end">
			<a href="<?= base_url ?>admin?page=items" class="btn btn-flat btn-default border-0 rounded00"><span class="fas fa-arrow-left"></span> Back to Items</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
			<div class="table-responsive">
				<table class="table table-sm table-hover table-striped table-bordered" id="claim-list">
					<colgroup>
						<col width="5%">
						<col width="20%">
						<col width="15%">
						<col width="35%">
						<col width="15%">
						<col width="10%">
					</colgroup>
					<thead>
						<tr>
							<th>#</th>
							<th>Requested At</th>
							<th>Item</th>
							<th>Claimant</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$qry = $conn->query("SELECT c.*, i.title as item_title FROM `claim_requests` c LEFT JOIN `item_list` i ON i.id = c.item_id ORDER BY c.created_at DESC");
						while($row = $qry->fetch_assoc()):
						?>
						<tr>
							<td class="text-center"><?php echo $i++; ?></td>
							<td><?php echo date("Y-m-d g:i A",strtotime($row['created_at'])) ?></td>
							<td><?= htmlspecialchars($row['item_title']) ?></td>
							<td>
								<strong><?= htmlspecialchars($row['name']) ?></strong><br>
								<?= htmlspecialchars($row['contact']) ?><br>
								<div class="small text-muted"><?= htmlspecialchars($row['message']) ?></div>
							</td>
							<td class="text-center">
								<?php if($row['status'] == 1): ?>
									<span class="badge bg-success">Approved</span>
								<?php elseif($row['status'] == 0): ?>
									<span class="badge bg-secondary">Pending</span>
								<?php else: ?>
									<span class="badge bg-muted">Unknown</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php if($row['status'] == 0): ?>
									<button class="btn btn-sm btn-success approve-claim" data-id="<?= $row['id'] ?>">Approve</button>
								<?php endif; ?>
								<a class="btn btn-sm btn-light" href="<?= base_url ?>admin?page=items/view_item&id=<?= $row['item_id'] ?>">View Item</a>
							</td>
						</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
	$(function(){
		const dT = new simpleDatatables.DataTable('#claim-list')
		$(document).on('click','.approve-claim',function(){
			_conf("Approve this claim and mark item as claimed?","approve_claim", [$(this).data('id')])
		})
	})
</script>
