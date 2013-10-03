//
//  StoriaStoryViewController.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 22/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaStoryViewController.h"
#import "UIImageView+AFNetworking.h"

@interface StoriaStoryViewController ()

@property (nonatomic, strong) UIScrollView *mainScrollView;
@property (nonatomic, strong) NSArray *imagesArray;

@end

@implementation StoriaStoryViewController

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
    self.mainScrollView = [[UIScrollView alloc] initWithFrame:self.view.frame];
    self.mainScrollView.pagingEnabled = YES;
    [self.view addSubview:self.mainScrollView];
    self.title = self.story.name;
	// Do any additional setup after loading the view.
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
    
    self.mainScrollView.contentSize = CGSizeMake(320 * self.story.imagesArray.count, self.mainScrollView.frame.size.height);
    CGFloat xOffset = 0;
    for (StoriaImage *storiaImage in self.story.imagesArray) {
        UIImageView *imageView = [[UIImageView alloc] initWithFrame:CGRectOffset(self.mainScrollView.frame, xOffset, -self.mainScrollView.frame.origin.y)];
        NSURLRequest *urlRequest = [[NSURLRequest alloc] initWithURL:[NSURL URLWithString:storiaImage.imageUrl]];
        __weak typeof(imageView) __weak_imageView = imageView;
        imageView.contentMode = UIViewContentModeScaleAspectFit;
        [imageView setImageWithURLRequest:urlRequest placeholderImage:[UIImage imageNamed:@"icon"] success:^(NSURLRequest *request, NSHTTPURLResponse *response, UIImage *image) {
            __weak_imageView.image = image;
        } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error) {
        }];
        [self.mainScrollView addSubview:imageView];
    
        CGRect captionLabelPossibleRect = [storiaImage.caption boundingRectWithSize:CGSizeMake(300, 100) options:NSStringDrawingUsesLineFragmentOrigin attributes:nil context:nil];
        UILabel *captionLabel = [[UILabel alloc] initWithFrame:CGRectMake(10 + xOffset, self.mainScrollView.frame.size.height - captionLabelPossibleRect.size.height - 20, 300, captionLabelPossibleRect.size.height + 20)];
        captionLabel.text = storiaImage.caption;
        captionLabel.numberOfLines = 0;
        captionLabel.textColor = [UIColor whiteColor];
        
        UIView *view = [[UIView alloc] initWithFrame:CGRectMake(0 + xOffset, captionLabel.frame.origin.y, 320, captionLabel.frame.size.height)];
        view.backgroundColor = [UIColor blackColor];
        view.alpha = 0.5f;
        CALayer *layer = [view layer];
        [layer setRasterizationScale:0.5];
        [layer setShouldRasterize:YES];
        
        [self.mainScrollView addSubview:view];
        [self.mainScrollView addSubview:captionLabel];
        
        xOffset += self.mainScrollView.frame.size.width;
    }
}

@end
