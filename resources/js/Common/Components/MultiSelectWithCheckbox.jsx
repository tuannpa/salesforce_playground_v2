import React, { useState } from 'react';
import Select, { components } from 'react-select';

const styles = {
    container: base => ({
        ...base,
        flex: 1
    })
};

const InputOption = ({
                         getStyles,
                         Icon,
                         isDisabled,
                         isFocused,
                         isSelected,
                         children,
                         innerProps,
                         ...rest
                     }) => {
    const [isActive, setIsActive] = useState(false);
    const onMouseDown = () => setIsActive(true);
    const onMouseUp = () => setIsActive(false);
    const onMouseLeave = () => setIsActive(false);

    // styles
    let bg = "transparent";
    if (isFocused) bg = "#eee";
    if (isActive) bg = "#B2D4FF";

    const style = {
        alignItems: "center",
        backgroundColor: bg,
        color: "inherit",
        display: "flex "
    };

    // prop assignment
    const props = {
        ...innerProps,
        onMouseDown,
        onMouseUp,
        onMouseLeave,
        style
    };

    return (
        <components.Option
            {...rest}
            isDisabled={isDisabled}
            isFocused={isFocused}
            isSelected={isSelected}
            getStyles={getStyles}
            innerProps={props}
        >
            <input className="me-2" type="checkbox" checked={isSelected}/>
            {children}
        </components.Option>
    );
};

export default function MultiSelectWithCheckbox(props) {
    const { form, field, options, className, placeholder } = props;

    return (
        <Select
            id={field.id}
            name={field.name}
            className={className}
            placeholder={placeholder}
            defaultValue={[]}
            isMulti
            styles={styles}
            closeMenuOnSelect={false}
            hideSelectedOptions={false}
            onBlur={field.onBlur}
            onChange={(options) => {
                if (Array.isArray(options)) {
                    form.setFieldValue(field.name, options.map((opt) => opt.value));
                }
            }}
            options={options}
            components={{
                Option: InputOption
            }}
        />
    );
}
