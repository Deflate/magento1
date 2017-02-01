<?php

/**
 * Class Udder_Deflate_Block_Adminhtml_Images_Grid
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Block_Adminhtml_Images_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('deflate_images_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->setUseAjax(true);

        $this->setEmptyText('You must run a scan on your Magento site before any images will appear here.');
    }

    /**
     * Retrieve all images from collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('udder_deflate/image_collection');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Build our various columns
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('created_at',
            array(
                'header'=> $this->__('Created At'),
                'index' => 'created_at',
                'type'      => 'datetime',
                'width' => '130px'
            )
        );

        $this->addColumn('compressed_at',
            array(
                'header'=> $this->__('Compressed At'),
                'index' => 'compressed_at',
                'type'      => 'datetime',
                'width' => '130px'
            )
        );

        $this->addColumn('name',
            array(
                'header'=> $this->__('File Name'),
                'index' => 'name'
            )
        );

        $this->addColumn('magento_type',
            array(
                'header'=> $this->__('Type'),
                'index' => 'magento_type',
                'type' => 'options',
                'options' => Udder_Deflate_Model_Scan::getTypesAsArray()
            )
        );

        $this->addColumn('original_size',
            array(
                'header'=> $this->__('Original Size'),
                'index' => 'original_size',
                'filter' => false,
                'width' => 100,
                'frame_callback' => array($this, 'displaySize')
            )
        );

        $this->addColumn('deflated_size',
            array(
                'header'=> $this->__('Deflated Size'),
                'index' => 'deflated_size',
                'filter' => false,
                'width' => 100,
                'frame_callback' => array($this, 'displaySize')
            )
        );

        $this->addColumn('difference',
            array(
                'header'=> $this->__('Difference'),
                'index' => 'difference',
                'filter' => false,
                'width' => 100,
                'frame_callback' => array($this, 'displayPercentage'),
                'sortable' => false
            )
        );

        $this->addColumn('status',
            array(
                'header'=> $this->__('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => Udder_Deflate_Model_Image::getStatusesAsArray(),
                'frame_callback' => array($this, 'decorateStatus'),
                'width' => 140
            )
        );

        $this->addColumn('action',
            array(
                'header'    =>  $this->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => $this->__('Compress'),
                        'url'       => array('base'=> '*/*/compress'),
                        'field'     => 'id'
                    ),
                    array(
                        'caption'   => $this->__('View'),
                        'url'       => array('base'=> '*/*/view'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    /**
     * Decorate the status column using the various built in classes
     * Static function as it's used by the answers grid
     *
     * @param $value
     * @param $row Udder_Deflate_Model_Image
     * @param $column
     * @param $isExport
     * @return string
     */
    public static function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getData('status')) {
            case Udder_Deflate_Model_Image::STATUS_PENDING:
            case Udder_Deflate_Model_Image::STATUS_REVERTED:
                $class = 'grid-severity-minor';
                break;
            case Udder_Deflate_Model_Image::STATUS_COMPRESSING:
                $class = 'grid-severity-minor';
                break;
            case Udder_Deflate_Model_Image::STATUS_COMPLETE:
                $class = 'grid-severity-notice';
                break;
            case Udder_Deflate_Model_Image::STATUS_FAILED:
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="' . $class . '"><span>' . $value . '</span></span>';
    }

    /**
     * Return the size in a human readable format
     *
     * @param $value
     * @param $row Udder_Deflate_Model_Image
     * @param $column
     * @param $isExport
     * @return string
     */
    public static function displaySize($value, $row, $column, $isExport)
    {
        if($value) {
            return Mage::helper('udder_deflate')->readableSize($value);
        }
        return '';
    }

    /**
     * Return the size in a human readable format
     *
     * @param $value
     * @param $row Udder_Deflate_Model_Image
     * @param $column
     * @param $isExport
     * @return string
     */
    public static function displayPercentage($value, $row, $column, $isExport)
    {
        if($row->getData('deflated_size')) {
            return ceil(Mage::helper('udder_deflate')->percentageDifference($row->getData('original_size'), $row->getData('deflated_size'))) . '%';
        }
        return '';
    }

    /**
     * Return the edit URL for a row
     *
     * @param $row Udder_Deflate_Model_Image
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        if($row->getData('deflated_size')) {
            return $this->getUrl('*/*/view', array('id' => $row->getId()));
        }

        return false;
    }

    /**
     * Return the grid URL for Ajax operations
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    /**
     * Add in the massaction details
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $modelPk = Mage::getResourceModel('udder_deflate/image')->getIdFieldName();
        $this->setMassactionIdField($modelPk);
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('compress', array(
            'label' => $this->__('Compress'),
            'url'   => $this->getUrl('*/*/massCompress'),
        ));

        return $this;
    }

}
