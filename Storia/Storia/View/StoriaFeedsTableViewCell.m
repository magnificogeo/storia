//
//  StoriaFeedsTableViewCell.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaFeedsTableViewCell.h"
#import "UIImageView+AFNetworking.h"

@interface StoriaFeedsTableViewCell()

@property (nonatomic, strong) UILabel *storyNameLabel;
//@property (nonatomic, strong) UIScrollView *mainScrollView;
//@property (nonatomic, strong) NSArray *imagesArray;
@property (nonatomic, strong) UIImageView *mainImageView;
@property (nonatomic, strong) UILabel *authorUserNameLabel;
@property (nonatomic, strong) UIImageView *authorProfileImageView;
@property (nonatomic, strong) UILabel *viewsCountLabel;
//@property (nonatomic, strong) UILabel *descriptionLabel;
//@property (nonatomic, strong) UIImage *backgroundImage;

@end

@implementation StoriaFeedsTableViewCell

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier
{
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if (self) {
        // Initialization code
        UIView *cellBackground = [[UIView alloc] initWithFrame:CGRectMake(0, 0, 320, 280)];
        cellBackground.backgroundColor = [UIColor whiteColor];
        CALayer *backgroundLayer = cellBackground.layer;
        [backgroundLayer setShadowOffset:CGSizeMake(0, 2)];
        [backgroundLayer setShadowColor:[UIColor blackColor].CGColor];
        [backgroundLayer setShadowOpacity:0.2];
        [backgroundLayer setShadowRadius:0.5];

        [self addSubview:cellBackground];
        self.storyNameLabel = [[UILabel alloc] initWithFrame:CGRectMake(10, 190, 300, 40)];
        self.storyNameLabel.numberOfLines = 0;
        self.storyNameLabel.textColor = [UIColor whiteColor];

        UIView *view = [[UIView alloc] initWithFrame:CGRectMake(0, 180, 320, 60)];
        view.backgroundColor = [UIColor blackColor];
        view.alpha = 0.5f;
        CALayer *layer = [view layer];
        [layer setRasterizationScale:0.5];
        [layer setShouldRasterize:YES];
        self.mainImageView = [[UIImageView alloc] initWithFrame:CGRectMake(0, 0, 320, 240)];
        self.mainImageView.contentMode = UIViewContentModeScaleAspectFill;
        
        self.authorProfileImageView = [[UIImageView alloc] initWithFrame:CGRectMake(20, 245, 30, 30)];
        self.authorUserNameLabel = [[UILabel alloc] initWithFrame:CGRectMake(60, 245, 220, 30)];
        self.viewsCountLabel = [[UILabel alloc] initWithFrame:CGRectMake(200, 245, 100, 30)];
        self.viewsCountLabel.textAlignment = NSTextAlignmentRight;
        
        [self addSubview:self.mainImageView];
        [self addSubview:view];
        [self addSubview:self.storyNameLabel];
        [self addSubview:self.authorUserNameLabel];
        [self addSubview:self.authorProfileImageView];
        [self addSubview:self.viewsCountLabel];
        self.backgroundColor = [UIColor colorWithWhite:0.9 alpha:1.0];
        self.selectionStyle = UITableViewCellSelectionStyleNone;
    }
    return self;
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated
{
    [super setSelected:selected animated:animated];

    // Configure the view for the selected state
}

- (UIImage *) cropImage:(UIImage *)image {
    CGSize newSize = CGSizeMake(640, image.size.height/image.size.width * 640);
    if (image.size.height/image.size.width * 640 < 480) {
        newSize = CGSizeMake(480*image.size.width/image.size.height, 480);
    }
    
    UIGraphicsBeginImageContext( newSize );
    [image drawInRect:CGRectMake(0,0,newSize.width,newSize.height)];
    UIImage* newImage = UIGraphicsGetImageFromCurrentImageContext();
    UIGraphicsEndImageContext();
    
    CGSize itemSize = CGSizeMake(640, 480);
    
    CGImageRef imageRef = CGImageCreateWithImageInRect([newImage CGImage], CGRectMake(0.0, 50.0, itemSize.width, itemSize.height));
    
    UIImage *cropped =[UIImage imageWithCGImage:imageRef];
    CGImageRelease(imageRef);
    return cropped;
}

- (void)reloadViewsWithStory:(StoriaStory *)story {
    self.storiaStory = story;
    self.storyNameLabel.text = self.storiaStory.name;
    self.authorUserNameLabel.text = self.storiaStory.author.userName;
    NSURLRequest *profilePictureRequest = [[NSURLRequest alloc] initWithURL:[NSURL URLWithString:self.storiaStory.author.profileImageUrl]];
    __weak typeof(self.authorProfileImageView) __weakProfilePicture = self.authorProfileImageView;
    [self.authorProfileImageView setImageWithURLRequest:profilePictureRequest placeholderImage:Nil success:^(NSURLRequest *request, NSHTTPURLResponse *response, UIImage *image) {
        __weakProfilePicture.image = image;
    } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error) {
        
    }];
    
    self.viewsCountLabel.text = [NSString stringWithFormat:@"%d views", arc4random() % 1000];
    
    NSURLRequest *urlRequest = [[NSURLRequest alloc] initWithURL:[NSURL URLWithString:self.storiaStory.backgroundImageUrl]];
    [self.mainImageView cancelImageRequestOperation];
    __weak typeof(self.mainImageView) __weak_imageView = self.mainImageView;
    __weak typeof(self) __weakSelf = self;
    [self.mainImageView setImageWithURLRequest:urlRequest placeholderImage:nil success:^(NSURLRequest *request, NSHTTPURLResponse *response, UIImage *image) {
        __weak_imageView.image = [__weakSelf cropImage:image];
    } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error) {
        
    }];
}

@end
