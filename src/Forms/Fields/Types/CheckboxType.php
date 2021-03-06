<?php

namespace Themosis\Forms\Fields\Types;

use Themosis\Forms\Contracts\FieldTypeInterface;
use Themosis\Forms\Fields\Contracts\CanHandleMetabox;
use Themosis\Forms\Transformers\StringToBooleanTransformer;

class CheckboxType extends BaseType implements CanHandleMetabox
{
    /**
     * CheckboxType field view.
     *
     * @var string
     */
    protected $view = 'types.checkbox';

    /**
     * Field type.
     *
     * @var string
     */
    protected $type = 'checkbox';

    /**
     * The component name.
     *
     * @var string
     */
    protected $component = 'themosis.fields.checkbox';

    /**
     * Parse field options.
     *
     * @param array $options
     *
     * @return array
     */
    protected function parseOptions(array $options): array
    {
        $this->setTransformer(new StringToBooleanTransformer());

        $options = parent::parseOptions($options);

        // Set some default CSS classes if chosen theme is "bootstrap".
        if (isset($options['theme']) && 'bootstrap' === $options['theme']) {
            $options['attributes']['class'] = isset($options['attributes']['class']) ?
                ' form-check-input' : 'form-check-input';
            $options['label_attr']['class'] = isset($options['label_attr']['class']) ?
                ' form-check-label' : 'form-check-label';
        }

        return $options;
    }

    /**
     * @inheritdoc
     *
     * @param string $value
     *
     * @return FieldTypeInterface
     */
    public function setValue($value): FieldTypeInterface
    {
        parent::setValue($value);

        if ($this->getValue()) {
            // The value is only set on the field when it fails
            // or when the option "flush" is set to "false".
            // If true, let's automatically add the "checked" attribute.
            $this->options['attributes']['checked'] = 'checked';
        }

        return $this;
    }

    /**
     * Handle checkbox field post meta registration.
     *
     * @param mixed $value
     * @param int   $post_id
     */
    public function metaboxSave($value, int $post_id)
    {
        $this->setValue($value);

        $previous = get_post_meta($post_id, $this->getName(), true);

        if (is_null($this->getValue()) || empty($this->getValue())) {
            delete_post_meta($post_id, $this->getName());
        } elseif (empty($previous)) {
            add_post_meta($post_id, $this->getName(), $this->getValue(), true);
        } else {
            update_post_meta($post_id, $this->getName(), $this->getValue(), $previous);
        }
    }

    /**
     * Initialize the checkbox field post meta value.
     *
     * @param int $post_id
     */
    public function metaboxGet(int $post_id)
    {
        $value = get_post_meta($post_id, $this->getName(), true);

        if (! empty($value)) {
            $this->setValue($value);
        }
    }
}
