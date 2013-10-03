//
//  StoriaStory.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaStory.h"
#import "StoriaImage.h"

@implementation StoriaStory

- (id)initWithStoryName:(NSString *)name description:(NSString *)description backgroundImageUrl:(NSString *)backgroundImageUrl ImageUrlsArray:(NSArray *)imageArray {
    if (self = [super init]) {
        self.name = name;
        self.description = description;
        self.backgroundImageUrl = backgroundImageUrl;
        NSMutableArray *tempArray = [NSMutableArray array];
        for (NSDictionary *dict in imageArray) {
            StoriaProfile *profile = [[StoriaProfile alloc] initWithUserName:dict[@"user_name"] realName:dict[@"real_name"] profileImageUrl:dict[@"profile_picture_url"]];
            StoriaImage *image = [[StoriaImage alloc] initWithImageUrl:dict[@"url"] caption:dict[@"caption"] author:profile];
            [tempArray addObject:image];
        }
        if (tempArray.count == 0)
            self.author = [StoriaProfile randomizeStoriaProfile];
        else
            self.author = [(StoriaImage *)[tempArray objectAtIndex:0] author];
        self.imagesArray = [NSArray arrayWithArray:tempArray];
    }
    return self;
}

+ (id)randomizeStoriaStory {
    return [[StoriaStory alloc] initWithStoryName:@"San Francisco" description:@"San Francisco is an amazing city" backgroundImageUrl:@"http://img.justthetravel.com/Justthetravel-Golden-Gate-Bridge-beautiful.jpg" ImageUrlsArray:[NSArray arrayWithObjects:[StoriaImage randomizeStoriaImage],[StoriaImage randomizeStoriaImage], nil]];
}

@end
