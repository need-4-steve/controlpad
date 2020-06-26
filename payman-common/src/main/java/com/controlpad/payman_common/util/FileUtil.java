package com.controlpad.payman_common.util;

import com.amazonaws.AmazonClientException;
import com.amazonaws.AmazonServiceException;
import com.amazonaws.auth.profile.ProfileCredentialsProvider;
import com.amazonaws.services.s3.AmazonS3;
import com.amazonaws.services.s3.AmazonS3Client;
import com.amazonaws.services.s3.model.GetObjectRequest;
import com.amazonaws.services.s3.model.ObjectMetadata;
import com.amazonaws.services.s3.model.PutObjectRequest;
import com.amazonaws.services.s3.model.S3Object;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.validation.constraints.NotNull;
import java.io.*;

public class FileUtil {
    private static final Logger logger = LoggerFactory.getLogger(FileUtil.class);
    private static final AmazonS3 s3Client = new AmazonS3Client(new ProfileCredentialsProvider("S3CredentialsProfile.txt", "s3key"));
    private static final String BUCKET_NAME = (Boolean.valueOf(System.getProperty("DEBUG")) ? "payman-payouts-debug" : "payman-payouts");

    public static String generateKeyName(@NotNull Long position, @NotNull String clientId, @NotNull String fileName){
        return String.valueOf(position) + "-" + clientId.toLowerCase().replaceAll("[^a-zA-Z0-9]", "") + "/" + fileName;
    }

    public static boolean uploadFile(String keyName, File uploadFile){
        try {
            System.out.println("Uploading a new object to S3 from a file\n");
            PutObjectRequest object = new PutObjectRequest(BUCKET_NAME, keyName, uploadFile);
            s3Client.putObject(object);
            return true;
        } catch (AmazonServiceException ase) {
            logger.error(String.format("Caught an AmazonServiceException: %s  bucketName-keyName-uploadFileName: %s-%s-%s", ase.getMessage(), BUCKET_NAME, keyName, uploadFile.getName()), ase);
        } catch (AmazonClientException ace) {
            logger.error(String.format("Caught an AmazonClientException: %s  bucketName-keyName-uploadFileName: %s-%s-%s", ace.getMessage(), BUCKET_NAME, keyName, uploadFile.getName()), ace);
        }
        return false;
    }

    public static boolean uploadFileStream(String keyName, InputStream inputStream){
        try {
            System.out.println("Uploading a new object to S3 from a file\n");

            PutObjectRequest object = new PutObjectRequest(BUCKET_NAME, keyName, inputStream, new ObjectMetadata());
            s3Client.putObject(object);
            return true;
        } catch (AmazonServiceException ase) {
            logger.error(String.format("Caught an AmazonServiceException: %s  bucketName-keyName: %s-%s", ase.getMessage(), BUCKET_NAME, keyName), ase);
        } catch (AmazonClientException ace) {
            logger.error(String.format("Caught an AmazonClientException: %s  bucketName-keyName: %s-%s", ace.getMessage(), BUCKET_NAME, keyName), ace);
        }
        return false;
    }

    public static boolean getFile(String keyName, OutputStream output){
        if(!s3Client.doesObjectExist(BUCKET_NAME, keyName)){
            return false;
        }
        S3Object s3Object = s3Client.getObject(new GetObjectRequest(BUCKET_NAME, keyName));
        try (InputStream inputStream = s3Object.getObjectContent();
             BufferedOutputStream outputStream = new BufferedOutputStream(output)){

            byte[] content = new byte[1024];
            int totalSize = 0;
            int bytesRead;
            while ((bytesRead = inputStream.read(content)) != -1) {
                System.out.println(String.format("%d bytes read from stream", bytesRead));
                outputStream.write(content, 0, bytesRead);
                totalSize += bytesRead;
            }
            System.out.println("Total Size of file in bytes = "+totalSize);
            return true;
        } catch (AmazonServiceException ase) {
            logger.error(String.format("Caught an AmazonServiceException: %s  bucketName-keyName: %s-%s", ase.getMessage(), BUCKET_NAME, keyName), ase);
        } catch (AmazonClientException ace) {
            logger.error(String.format("Caught an AmazonClientException: %s  bucketName-keyName: %s-%s", ace.getMessage(), BUCKET_NAME, keyName), ace);
        } catch (Exception e){
            logger.error("FileUtil.getFile: ", e);
        }
        return false;
    }

    public static boolean getFile(String keyName, File file) {
        try (OutputStream os = new BufferedOutputStream(new FileOutputStream(file))) {
            return getFile(keyName, os);
        } catch (IOException ioe) {
            logger.error(ioe.getMessage(), ioe);
        }
        return false;
    }
}