<?php

namespace Jb\Bundle\PhumborBundle\Manager;

use Doctrine\ORM\EntityManager;
use Jb\Bundle\PhumborBundle\Entity\PhumborAsset;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Kernel;

class PhumborAssetManager {

    protected $serverUrl;

    protected $serverKey;

    protected $uploadAuthUsername;

    protected $uploadAuthPassword;

    /* @var $em EntityManager */
    protected $em;

    /* @var $client \GuzzleHttp\Client|\Guzzle\Http\Client */
    protected $client;

    /* @var $kernel Kernel */
    protected $kernel;

    public function __construct($server_url, $server_key, $upload_auth_username, $upload_auth_password, Kernel $kernel, EntityManager $em) {
        $this->serverUrl = $server_url;
        $this->serverKey = $server_key;
        $this->uploadAuthUsername = $upload_auth_username;
        $this->uploadAuthPassword = $upload_auth_password;
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
            $config = [
                'Content-Type' => $file->getMimeType(),
                'Slug' => $file->getFilename(),
                'body' => file_get_contents($file->getRealPath())
            ];

            if ($this->uploadAuthUsername && $this->uploadAuthPassword) {
                $config['auth'] = [
                    'username' => $this->uploadAuthUsername,
                    'password' => $this->uploadAuthPassword
                ];
            }

            $response = $this->client->post('/image', $config);

            $remote_path = $response->getHeader('Location')[0];
        } else {
            $request = $this->client->post('/image', [
                'Content-Type' => $file->getMimeType(),
                'Slug' => $file->getFilename()
            ], file_get_contents($file->getRealPath()));


            if ($this->uploadAuthUsername && $this->uploadAuthPassword) {
                $request->setAuth($this->uploadAuthUsername, $this->uploadAuthPassword);
            }

            $response = $request->send();

            $remote_path = (string)$response->getHeader('Location');
        }

        $thumborAsset->setRemotePath($remote_path);
        $this->em->persist($thumborAsset);
        $this->em->flush();

        dump('uploaded successfully');

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

        $local_hash = sha1_file($file->getRealPath());
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
        $webdir = $this->kernel->getContainer()->getParameter('phumbor.publicroot');

        return new File($webdir.$local_web_path);
    }

}