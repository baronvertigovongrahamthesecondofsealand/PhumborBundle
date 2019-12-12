<?php

namespace Jb\Bundle\PhumborBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PhumborAsset
 *
 * @ORM\Table(name="app_phumbor_asset")
 * @ORM\Entity(repositoryClass="Jb\Bundle\PhumborBundle\Repository\PhumborAssetRepository")
 */
class PhumborAsset {

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="local_hash", type="string", length=255)
     */
    private $localHash;

    /**
     * @var string
     *
     * @ORM\Column(name="local_path", type="string", length=255)
     */
    private $localPath;

    /**
     * @var string
     *
     * @ORM\Column(name="remote_path", type="string", length=255)
     */
    private $remotePath;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     * @return PhumborAsset
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocalHash() {
        return $this->localHash;
    }

    /**
     * @param string $localHash
     * @return PhumborAsset
     */
    public function setLocalHash($localHash) {
        $this->localHash = $localHash;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocalPath() {
        return $this->localPath;
    }

    /**
     * @param string $localPath
     * @return PhumborAsset
     */
    public function setLocalPath($localPath) {
        $this->localPath = $localPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getRemotePath() {
        return $this->remotePath;
    }

    /**
     * @param string $remotePath
     * @return PhumborAsset
     */
    public function setRemotePath($remotePath) {
        $this->remotePath = $remotePath;

        return $this;
    }

}
