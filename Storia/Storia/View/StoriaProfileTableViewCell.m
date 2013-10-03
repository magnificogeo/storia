//
//  StoriaProfileTableViewCell.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 22/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaProfileTableViewCell.h"
#import "UIImageView+AFNetworking.h"

@interface StoriaProfileTableViewCell()

@property (nonatomic, strong) UILabel *userNameLabel;
@property (nonatomic, strong) UILabel *realNameLabel;
@property (nonatomic, strong) UIImageView *profileImageView;

@end

@implementation StoriaProfileTableViewCell

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier
{
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if (self) {
        // Initialization code
        UIView *cellBackground = [[UIView alloc] initWithFrame:CGRectMake(0, 0, 320, 180)];
        cellBackground.backgroundColor = [UIColor whiteColor];
        CALayer *backgroundLayer = cellBackground.layer;
        [backgroundLayer setShadowOffset:CGSizeMake(0, 2)];
        [backgroundLayer setShadowColor:[UIColor blackColor].CGColor];
        [backgroundLayer setShadowOpacity:0.2];
        [backgroundLayer setShadowRadius:0.5];
        
        [self addSubview:cellBackground];
        
        self.userNameLabel = [[UILabel alloc] initWithFrame:CGRectMake(100, 20, 200, 20)];
        self.realNameLabel = [[UILabel alloc] initWithFrame:CGRectMake(100, 60, 200, 20)];
        self.profileImageView = [[UIImageView alloc] initWithFrame:CGRectMake(20, 20, 60, 60)];
        
        [self addSubview:self.userNameLabel];
        [self addSubview:self.realNameLabel];
        [self addSubview:self.profileImageView];
        
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

- (void)updateWithProfile:(StoriaProfile *)profile {
    self.userNameLabel.text = profile.userName;
    self.realNameLabel.text = profile.realName;
    [self.profileImageView cancelImageRequestOperation];
    __weak typeof(self.profileImageView) __weakProfileImageView = self.profileImageView;
    NSURLRequest *urlRequest = [[NSURLRequest alloc] initWithURL:[NSURL URLWithString:profile.profileImageUrl]];
    [self.profileImageView setImageWithURLRequest:urlRequest placeholderImage:[UIImage imageNamed:@"icon"] success:^(NSURLRequest *request, NSHTTPURLResponse *response, UIImage *image) {
        __weakProfileImageView.image = image;
    } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error) {
        
    }];
}

@end
