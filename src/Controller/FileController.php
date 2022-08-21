<?php

namespace App\Controller;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\String\Slugger\SluggerInterface;
use Intervention\Image\ImageManagerStatic as Image;
use Aws\S3\S3Client;
use Ramsey\Uuid\Uuid;

class FileController extends AbstractController
{


    #[Route('/file/upload', name: 'app_file_upload', methods: ['POST'])]
    public function upload(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {

        $s3 = false;

        // Amazon AWS S3 Test
        // ACL must be fixed
        // 21.08.22
        // $s3 = true;

        $allowedMimes = [
            "image/jpeg", "image/png", "image/gif", "application/pdf"
        ];

        $uploadedFile = $request->files->get('file');

        $violations = $validator->validate(
            $uploadedFile,
            new FileConstraint([
                'maxSize' => '5M',
                'mimeTypes' => $allowedMimes
            ])
        );

        if ($uploadedFile) {

            $guid = $uuid = Uuid::uuid4();
            
            $fileMimeType = $uploadedFile->getMimeType();
            $fileSize = $uploadedFile->getSize();
            $fileExtension = strtolower($uploadedFile->guessExtension());
            $newFileName = $guid;

            if($s3) {

                $s3 = new S3Client([
                    'version' => 'latest',
                    'region'  => $this->getParameter('aws_s3_region'),
                    'credentials' => [
                        'key'    => $this->getParameter('aws_s3_key'),
                        'secret' => $this->getParameter('aws_s3_secret')
                    ]
                ]);

                try {

                    $s3->putObject([
                        'Bucket' => $this->getParameter('aws_s3_bucket'),
                        'Key' => $guid . '.' . $fileExtension,
                        'Body' => fopen($uploadedFile->getPathname(), 'r'),
                        //'ACL'    => 'public-read'
                    ]);
                    
                    $file = new File();
                    $file->setMimeType($fileMimeType);
                    $file->setSource('amazon_s3');
                    $file->setFileName($newFileName);
                    $file->setExtension($fileExtension);
                    $file->setFileSize($fileSize);
                    $file->setCdnHost($s3->getEndpoint()->getHost());
                    $file->setCdnAccount($this->getParameter('aws_s3_bucket'));
                    $file->setCdnServer($this->getParameter('aws_s3_region'));
                    $file->setDate(new \DateTime());

                    $entityManager->persist($file);
                    $entityManager->flush();

                }
                catch (Aws\S3\Exception\S3Exception $e) {

                    // ...

                }

            }
            else {
                
                try {

                    $uploadedFile->move(
                        $this->getParameter('local_cdn'),
                        $newFileName . '.' . $fileExtension
                    );
                    

                    if(stripos($fileMimeType, 'image/') !== false) {
                        
                        $image = Image::make($this->getParameter('local_cdn') . '/' . $newFileName . '.' . $fileExtension);
                        $imageHeight = $image->height();
                        $imageWidth = $image->width();
                        $image->fit(240, 240);
                        $image->save($this->getParameter('local_cdn') . '/' . $newFileName . '_thumb.' . $fileExtension);
                        
                        $newImageHeight = 200;
                        $imageRatio = $newImageHeight / $imageHeight;
                        $newImageWidth = round($imageWidth * $imageRatio);

                        $image = Image::make($this->getParameter('local_cdn') . '/' . $newFileName . '.' . $fileExtension);
                        $imageHeight = $image->height();
                        $imageWidth = $image->width();
                        $image->fit($newImageWidth, $newImageHeight);
                        $image->save($this->getParameter('local_cdn') . '/' . $newFileName . '_thumb_h.' . $fileExtension);

                    }

                    $file = new File();
                    $file->setMimeType($fileMimeType);
                    $file->setSource('local_cdn');
                    $file->setFileName($newFileName);
                    $file->setExtension($fileExtension);
                    $file->setFileSize($fileSize);
                    $file->setDate(new \DateTime());

                    $entityManager->persist($file);
                    $entityManager->flush();

                }
                catch (FileException $e) {
                    // ...
                }
                
                $encoder = new JsonEncoder();
                $defaultContext = [
                    AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                        return null;
                    }
                ];

                $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
                $serializer = new Serializer([$normalizer], [$encoder]);
        
                return new JsonResponse(json_decode($serializer->serialize($file, 'json')));

            }

        }

        return new JsonResponse(json_decode(['success' => false]));

    }


    #[Route('/file', name: 'app_file')]
    public function index(): Response
    {
        return $this->render('file/index.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }
}
