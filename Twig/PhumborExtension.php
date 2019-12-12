<?php

namespace Jb\Bundle\PhumborBundle\Twig;

use Jb\Bundle\PhumborBundle\Transformer\BaseTransformer;
use Jb\Bundle\PhumborBundle\Transformer\Exception\UnknownTransformationException;
use Jb\Bundle\PhumborBundle\Manager\PhumborAssetManager;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Description of PhumborExtension
 *
 * @author jobou
 */
class PhumborExtension extends \Twig_Extension
{
    /**
     * @var \Jb\Bundle\PhumborBundle\Transformer\BaseTransformer
     */
    protected $transformer;

    /* @var $kernel Kernel */
    protected $kernel;

    /** @var  PhumborAssetManager */
    private $phumborAssetManager;

    /**
     * Constructor
     *
     * @param \Jb\Bundle\PhumborBundle\Transformer\BaseTransformer $transformer
     * @param Kernel
     * @param PhumborAssetManager
     */
    public function __construct(BaseTransformer $transformer, Kernel $kernel, PhumborAssetManager $phumborAssetManager)
    {
        $this->transformer = $transformer;
        $this->kernel = $kernel;
        $this->phumborAssetManager = $phumborAssetManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('thumbor', array($this, 'transform')),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('thumbor', array($this, 'transform')),
        );
    }

    /**
     * Twig thumbor filter
     *
     * @param string $orig
     * @param string $transformation
     * @param array $overrides
     *
     * @return string
     *
     * @throws UnknownTransformationException
     */
    public function transform($orig, $transformation = null, $overrides = array())
    {
        $enable_upload = $this->kernel->getContainer()->getParameter('phumbor.server.upload_enabled');

        if ($enable_upload) {
            $is_dev_env         = $this->kernel->getEnvironment() == 'dev';
            $is_local_filepath  = strpos($orig, 'http') !== 0;

            if ($is_dev_env && $is_local_filepath) {
                $thumborAsset = $this->phumborAssetManager->get($orig);
                $orig = $this->phumborAssetManager->getServerUrl().$thumborAsset->getRemotePath();
            }
        }

        return $this->transformer->transform($orig, $transformation, $overrides);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'phumbor_extension';
    }
}
