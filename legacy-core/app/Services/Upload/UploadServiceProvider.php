<?php namespace App\Services\Upload;

class UploadServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        // this allows us to simply invoke the upload interface and
        // regardless of what upload service is used, we know it
        // will contain the methods described in the interface
        
        // currently, bind the interface to Amazon S3. But if at a
        // later time we'd like to change to something else
        // (e.g. rackspace), changing this is all we need
        $this->app->bind('UploadInterface', 'UploadAmazonS3');
    }
}
