<?php
// Simple paginated view
$page = $this->input->get('page') ?: 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get candidates
$filters = [];
if (!empty($filter_job_id)) {
    $filters['job_id'] = $filter_job_id;
}

// Set filters
$this->Model_candidates->set_current_filters($filters);

// Get data
$candidates = $this->Model_candidates->get_all($limit, $offset, 'first_name', 'ASC', $filters);
$total = $this->Model_candidates->count_all();
$total_pages = ceil($total / $limit);
?>

<!-- Your table here (copy from existing view_list.php) -->
<table class="table table-striped">
    <!-- Your table headers -->
    <tbody>
        <?php foreach ($candidates->result() as $row): ?>
        <tr>
            <!-- Your columns -->
            <td><?php echo $row->reference_number; ?></td>
            <td><?php echo $row->first_name; ?></td>
            <!-- ... etc ... -->
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- SIMPLE PAGINATION -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
    <a href="?page=<?php echo $page-1; ?>">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
        <?php echo $i; ?>
    </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
    <a href="?page=<?php echo $page+1; ?>">Next</a>
    <?php endif; ?>
</div>
<?php endif; ?>