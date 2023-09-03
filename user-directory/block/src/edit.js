import {__} from '@wordpress/i18n';
import {useBlockProps, InspectorControls} from '@wordpress/block-editor';
import {
    CheckboxControl,
    PanelBody,
    SelectControl,
    __experimentalNumberControl
} from '@wordpress/components';

import ServerSideRender from '@wordpress/server-side-render';
import {withSelect} from '@wordpress/data';
import {createElement} from '@wordpress/element';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
function Edit(props) {
    const { attributes, setAttributes } = props;

    const blockProps = useBlockProps();

    const toggleField = function (fieldName) {
        let updatedFields = [...attributes.fields];

        // Check if the field is already in the array
        const index = updatedFields.indexOf(fieldName);

        if (index !== -1) {
            // If it exists, remove it
            updatedFields.splice(index, 1);
        } else {
            // If it doesn't exist, add it
            updatedFields.push(fieldName);
        }

        setAttributes({fields: updatedFields});
    };

    const toggleFilter = function (filterName) {
        let updatedFilters = [...attributes.filters];

        // Check if the field is already in the array
        const index = updatedFilters.indexOf(filterName);

        if (index !== -1) {
            // If it exists, remove it
            updatedFilters.splice(index, 1);
        } else {
            // If it doesn't exist, add it
            updatedFilters.push(filterName);
        }

        setAttributes({filters: updatedFilters});
    };

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('User Directory Settings', 'user-tag')} initialOpen={true}>
                    <SelectControl
                        label="User Role"
                        value={attributes.role}
                        options={
                        Object.keys(userDir.user_role).map((role) => ({
                            label: userDir.user_role[role],
                            value: role,
                        }))}
                        onChange={(value) => setAttributes({ role: value })}
                    />
                    <h2>Filters</h2>
                    {Object.keys(userDir.filters).map( function(filterName) {
                        return <CheckboxControl
                            key={filterName}
                            label={userDir.filters[filterName].label}
                            checked={attributes.filters.indexOf(filterName) !== -1}
                            onChange={() => toggleFilter(filterName)}
                        />
                    }
                    )}

                    <h2>Fields</h2>
                    {Object.keys(userDir.fields).map((fieldName) => (
                        <CheckboxControl
                            key={fieldName}
                            label={userDir.fields[fieldName].label.label || userDir.fields[fieldName].label }
                            checked={attributes.fields.indexOf(fieldName) !== -1}
                            onChange={() => toggleField(fieldName)}
                        />
                    ))}
                    <SelectControl
                        value={props.attributes.filters_logic}
                        label={__('Filters Logic', 'user-tags')}
                        onChange={
                            function (value) {
                                props.setAttributes({filters_logic: value});
                            }
                        }
                        options={[
                            {value: '', label: __('AND', 'user-tags')},
                            {value: 'or', label: __('OR', 'user-tags')},
                        ]}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <ServerSideRender
                    block="user-tags/user-directory"
                    attributes={attributes}
                />
            </div>
        </>
    );
}

// Use withSelect to fetch categories
const userCategories = withSelect((select) => {
    const { getEntityRecords } = select('core');
    const categories = getEntityRecords('taxonomy', 'category', { per_page: -1 });

    return {
        categories,
    };
})(Edit);

export default userCategories;
