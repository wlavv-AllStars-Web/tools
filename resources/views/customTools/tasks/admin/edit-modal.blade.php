<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaskForm" method="post">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- campos preenchidos dinamicamente via JS --}}
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.btn-edit-task', function() {
        let task = $(this).data('task'); // task JSON enviado via data-task
        let form = $('#editTaskForm');
    
        form.attr('action', task.update_url);
        form.find('input[name=title]').val(task.title);
        form.find('select[name=id_team]').val(task.id_team);
        form.find('select[name=assigned_user_id]').val(task.assigned_user_id);
        form.find('input[name=time_allowed]').val(task.time_allowed);
        form.find('select[name=status_admin]').val(task.status_admin);
        form.find('textarea[name=observations_admin]').val(task.observations_admin);
    
        $('#editTaskModal').modal('show');
    });

</script>