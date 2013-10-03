//
//  StoriaFeedsTableViewCell.h
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "StoriaStory.h"

@interface StoriaFeedsTableViewCell : UITableViewCell

@property (nonatomic, strong) StoriaStory *storiaStory;

- (void)reloadViewsWithStory:(StoriaStory *)story;

@end
