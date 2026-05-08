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
		<h3 class="card-title">Rechived Items</h3>
		<div class="card-tools d-flex justify-content-end">
			<a href="<?= base_url ?>admin?page=items" class="btn btn-flat btn-default border-0 rounded00"><span class="fas fa-arrow-left"></span> Back to Items</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
			<div class="table-responsive">
				<table class="table table-sm table-hover table-striped table-bordered" id="received-list">
					<colgroup>
						<col width="5%">
						<col width="20%">
						<col width="20%">
						<col width="25%">
						<col width="15%">
						<col width="15%">
					</colgroup>
					<thead>
						<tr>
							<th>#</th>
							<th>Rechived At</th>
							<th>Item</th>
							<th>Claimant</th>
							<th>Contact</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$qry = $conn->query("SELECT i.id as item_id, i.title, cr.name, cr.contact, cr.approved_at FROM `item_list` i JOIN `claim_requests` cr ON cr.item_id = i.id AND cr.status = 1 WHERE i.status = 2 ORDER BY cr.approved_at DESC");
						while($row = $qry->fetch_assoc()):
						?>
						<tr>
							<td class="text-center"><?php echo $i++; ?></td>
							<td><?php echo date("Y-m-d g:i A",strtotime($row['approved_at'])) ?></td>
							<td><?= htmlspecialchars($row['title']) ?></td>
							<td><?= htmlspecialchars($row['name']) ?></td>
							<td><?= htmlspecialchars($row['contact']) ?></td>
							<td class="text-center">
								<a class="btn btn-sm btn-light" href="<?= base_url ?>admin?page=items/view_item&id=<?= $row['item_id'] ?>">View</a>
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
    const dT = new simpleDatatables.DataTable('#received-list')
})
</script>
