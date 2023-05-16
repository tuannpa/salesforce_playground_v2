import React from 'react';

export default function EditableTextCell(props) {
    const { onCellChange, placeholder, value, fieldName } = props;

    return (
        <input
            name={fieldName}
            value={value}
            type="text"
            onChange={onCellChange}
            placeholder={placeholder}
        />
    );
}
