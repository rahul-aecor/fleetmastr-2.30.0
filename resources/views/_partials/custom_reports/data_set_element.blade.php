<tr>
    <td>
        <div class="checkbox-list">
            <label>
                <input type="checkbox" name="field_name[]" class="js-checkbox" {{ 'checked' }} data-dataset-id="{{ $data->id }}" value="{{ $data->id }}" {{ $isDisabled ? 'disabled' : ''}}>
                {{ $data->title }}
            </label>
        </div>
    </td>
    <td>
        {{ $data->description }}
    </td>
</tr>