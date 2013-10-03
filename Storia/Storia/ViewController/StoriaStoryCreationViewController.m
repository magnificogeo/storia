//
//  StoriaStoryCreationViewController.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 22/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaStoryCreationViewController.h"
#import <AWSS3/AWSS3.h>
#import <AWSRuntime/AWSRuntime.h>
#import "SVProgressHUD.h"
#import "StoriaClient.h"

@interface StoriaStoryCreationViewController () <UIImagePickerControllerDelegate, UINavigationControllerDelegate, AmazonServiceRequestDelegate, UITextViewDelegate>
@property (weak, nonatomic) IBOutlet UIScrollView *mainScrollView;
@property (strong, nonatomic) NSMutableArray *imagesArray;
@property (strong, nonatomic) NSMutableArray *imagesUrl;
@property (strong, nonatomic) NSMutableArray *captionsArray;
@property (strong, nonatomic) AmazonS3Client *s3Client;
@property (nonatomic, assign) int countFinishedUploads;
@end

@implementation StoriaStoryCreationViewController

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Custom initialization
    }
    return self;
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    self.imagesArray = [NSMutableArray array];
    self.captionsArray = [NSMutableArray array];
    
    self.s3Client = [[AmazonS3Client alloc] initWithAccessKey:S3_ACCESS_KEY withSecretKey:S3_SECRET_KEY];
    self.s3Client.endpoint = [AmazonEndpoints s3Endpoint:US_WEST_1];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (void)viewDidAppear:(BOOL)animated {
    [super viewDidAppear:animated];
    CGFloat navBarHeight = self.navigationController.navigationBar.frame.size.height;
    CGFloat statusBarHeight = [[UIApplication sharedApplication] statusBarFrame].size.height;
    CGFloat tabBarHeight = self.tabBarController.tabBar.frame.size.height;
    self.mainScrollView.frame = CGRectMake(0, navBarHeight + statusBarHeight, 320, self.view.frame.size.height - navBarHeight - statusBarHeight - tabBarHeight);
    self.mainScrollView.pagingEnabled = YES;
}

- (IBAction)takeFromCameraPressed:(id)sender {
    UIImagePickerController *imagePickerController = [[UIImagePickerController alloc] init];
    imagePickerController.sourceType = UIImagePickerControllerSourceTypeCamera;
    imagePickerController.allowsEditing = NO;
    imagePickerController.delegate = self;
    imagePickerController.navigationBar.tintColor = [UIColor redColor];
    [self presentViewController:imagePickerController animated:YES completion:nil];
}

- (IBAction)takeFromPhotosPressed:(id)sender {
    UIImagePickerController *imagePickerController = [[UIImagePickerController alloc] init];
    imagePickerController.sourceType = UIImagePickerControllerSourceTypePhotoLibrary;
    imagePickerController.allowsEditing = NO;
    imagePickerController.delegate = self;
    imagePickerController.navigationBar.tintColor = [UIColor redColor];
    [self presentViewController:imagePickerController animated:YES completion:nil];

}

- (void)imagePickerController:(UIImagePickerController *)picker didFinishPickingMediaWithInfo:(NSDictionary *)info {
    DLog(@"%@", info);
    [picker dismissViewControllerAnimated:YES completion:nil];
    UIImageView *imageView = [[UIImageView alloc] initWithFrame:CGRectMake(self.mainScrollView.frame.size.width * self.imagesArray.count, 0, self.mainScrollView.frame.size.width, self.mainScrollView.frame.size.height)];
    imageView.image = info[UIImagePickerControllerOriginalImage];
    [self.mainScrollView addSubview:imageView];
    [self.imagesArray addObject:info[UIImagePickerControllerOriginalImage]];
    UITextView *textView = [[UITextView alloc] initWithFrame:CGRectMake(imageView.frame.origin.x + 20, 80, 280, 60)];
    textView.backgroundColor = [UIColor colorWithWhite:0.9 alpha:0.5];
    textView.tag = self.imagesArray.count + 1000;
    textView.delegate = self;
    [self.mainScrollView addSubview:textView];
    self.mainScrollView.contentSize = CGSizeMake(self.imagesArray.count * self.mainScrollView.frame.size.width, self.mainScrollView.frame.size.height);
}

- (IBAction)publishButtonPressed:(id)sender {
    self.countFinishedUploads = 0;
    self.imagesUrl = [NSMutableArray array];
    if (self.imagesArray.count > 0) {
        UIImage *image = (UIImage *)[self.imagesArray objectAtIndex:0];
        // Convert the image to JPEG data.
        NSData *imageData = UIImageJPEGRepresentation(image, 1.0);
        [self processDelegateUpload:imageData];
    }
}

- (void)processDelegateUpload:(NSData *)imageData
{
    // Upload image data.  Remember to set the content type.
    NSString *imageName = [NSString stringWithFormat:@"%d%d.jpg", arc4random(), arc4random()];
    [self.imagesUrl addObject:imageName];
    S3PutObjectRequest *por = [[S3PutObjectRequest alloc] initWithKey:imageName
                                                             inBucket:S3_BUCKET_NAME];
    por.contentType = @"image/jpeg";
    por.data = imageData;
    por.cannedACL   = [S3CannedACL publicRead];
    por.delegate = self;
//
    // Put the image data into the specified s3 bucket and object.
    [self.s3Client putObject:por];
    [SVProgressHUD showProgress:0.0 status:@"Uploading..."];
}

- (void)request:(AmazonServiceRequest *)request didCompleteWithResponse:(AmazonServiceResponse *)response {
    self.countFinishedUploads++;
    if (self.countFinishedUploads == self.imagesArray.count) {
        [SVProgressHUD dismiss];
        [self pushRequestToServer];
    } else {
        UIImage *image = (UIImage *)[self.imagesArray objectAtIndex:self.countFinishedUploads];
        // Convert the image to JPEG data.
        NSData *imageData = UIImageJPEGRepresentation(image, 1.0);
        [self processDelegateUpload:imageData];
    }
}

- (void)request:(AmazonServiceRequest *)request didFailWithError:(NSError *)error {
    [SVProgressHUD showErrorWithStatus:@"Image was not uploaded successfully. Please try again"];
}

- (void)request:(AmazonServiceRequest *)request didSendData:(long long)bytesWritten totalBytesWritten:(long long)totalBytesWritten totalBytesExpectedToWrite:(long long)totalBytesExpectedToWrite {
    double percentage = (double)totalBytesWritten / totalBytesExpectedToWrite;
    [SVProgressHUD showProgress:percentage status:[NSString stringWithFormat:@"Uploading file %d/%d", self.countFinishedUploads + 1, self.imagesArray.count]];
}

- (void)pushRequestToServer {
    NSMutableDictionary *dict = [NSMutableDictionary dictionary];
    for (int i = 0; i < self.imagesArray.count; i++) {
        UITextView *textView = (UITextView *)[self.view viewWithTag:1000+i+1];
        [dict setValue:textView.text forKey:[NSString stringWithFormat:@"caption%d", i+1]];
        [dict setValue:[NSString stringWithFormat:@"http://storia.s3-us-west-1.amazonaws.com/%@", [self.imagesUrl objectAtIndex:i]]
                forKey:[NSString stringWithFormat:@"url%d", i+1]];
    }
    [StoriaClient submitStoryWithParams:dict WithSuccess:^(NSURLRequest *request, NSHTTPURLResponse *response, id JSON) {
        DLog(@"OK");
    } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error, id JSON) {
        DLog(@"FAILED");
    }];
}

@end
