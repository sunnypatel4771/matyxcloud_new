<div id="<?php echo $filters_wrapper_id ?? 'tasksFilters'; ?>" class="tw-inline pull-right tw-ml-0 sm:tw-ml-1.5">
    <?php
    $filter_array = $tasks_table->filters();

    // Make sure it is array
    if (!is_array($filter_array)) {
        $filter_array = [];
    }

    // Sort only tasks
    if ($tasks_table->id() === 'tasks') {
        usort($filter_array, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
    }

    // Convert back to JS format properly
    $sorted_filter_data = \app\services\utilities\Js::from($filter_array);

    ?>
    <app-filters 
        id="<?php echo $tasks_table->id(); ?>" 
        view="<?php echo $tasks_table->viewName(); ?>"
        :saved-filters="<?php echo $sorted_filter_data; ?>"
        :available-rules="<?php echo $tasks_table->rulesJs(); ?>">
    </app-filters>
</div>
<script>
    if(typeof(vNewApp) == 'function'){
        vNewApp('#tasksFilters')
    }
</script>