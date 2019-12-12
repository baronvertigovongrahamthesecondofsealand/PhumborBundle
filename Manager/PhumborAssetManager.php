<?php

namespace Jb\Bundle\PhumborBundle\Manager;

use Doctrine\ORM\EntityManager;
use Jb\Bundle\PhumborBundle\Entity\PhumborAsset;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Kernel;

class PhumborAssetManager {

    protected $serverUrl;

    protected $serverKey;

    /* @var $em EntityManager */
    protected $em;

    /* @var $client \GuzzleHttp\Client|\Guzzle\Http\Client */
    protected $client;

    /* @var $kernel Kernel */
    protected $kernel;

    public function __construct($server_url, $server_key, Kernel $kernel, EntityManager $em) {
        $this->serverUrl = $server_url;
        $this->serverKey = $server_key;
        $this->kernel = $kernel;
        $this->em = $em;

        if (Kernel::VERSION_ID >= 30000) {
            $this->client = new \GuzzleHttp\Client([
                'base_uri' => $this->serverUrl
            ]);
        } else {
            $this->client = new \Guzzle\Http\Client($this->serverUrl);
        }
    }

    public function getServerUrl() {
        return $this->serverUrl;
    }

    public function upload(PhumborAsset $thumborAsset) {
        $file = $this->getFile($thumborAsset->getLocalPath());

        if (Kernel::VERSION_ID >= 30000) {
            $response = $this->client->post('/image', [
                'Content-Type' => $file->getMimeType(),
                'Slug' => $file->getFilename(),
                'body' => file_get_contents($file->getPathname())
            ]);

            $remote_path = $response->getHeader('Location')[0];
        } else {
            $request = $this->client->post('/image', [
                'Content-Type' => $file->getMimeType(),
                'Slug' => $file->getFilename()
            ], file_get_contents($file->getPathname()));

            $response = $request->send();

            $remote_path = (string)$response->getHeader('Location');
        }

        $thumborAsset->setRemotePath($remote_path);
        $this->em->persist($thumborAsset);
        $this->em->flush();

        return $thumborAsset;
    }

    public function remove(PhumborAsset $thumborAsset) {
        $this->client->delete($thumborAsset->getRemotePath());
        $this->em->remove($thumborAsset);
        $this->em->flush();

        return null;
    }

    public function get($local_web_path) {
        $local_web_path = preg_replace(':^/:', '', $local_web_path);

        $file = $this->getFile($local_web_path);

        $thumborAsset = $this->em->getRepository('JbPhumborBundle:PhumborAsset')->findOneBy([
            'localPath' => $local_web_path
        ]);

        $local_hash = sha1_file($file->getPathname());
        if ($thumborAsset && $thumborAsset->getLocalHash() != $local_hash) {
            $thumborAsset = $this->remove($thumborAsset);
        }

        if (!$thumborAsset) {
            $thumborAsset = new PhumborAsset();
            $thumborAsset->setLocalPath($local_web_path);
            $thumborAsset->setLocalHash(sha1_file($file->getRealPath()));

            $this->upload($thumborAsset);
        }

        return $thumborAsset;
    }

    protected function getFile($local_web_path) {
        return new File($this->kernel->getRootDir().'/../web/'.$local_web_path);
    }

}