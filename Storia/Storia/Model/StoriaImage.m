//
//  StoriaImage.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaImage.h"

@implementation StoriaImage

- (id) initWithImageUrl:(NSString *)imageUrl caption:(NSString *)caption author:(StoriaProfile *)author {
    if (self = [super init]) {
        self.imageUrl = imageUrl;
        self.caption = caption;
        self.author = author;
    }
    return self;
}

+ (id) randomizeStoriaImage {
    StoriaImage *image = [[StoriaImage alloc] initWithImageUrl:@"http://dunesandduchess.com/wp-content/uploads/2012/05/golden-gate-bridge-san-francisco-ca.jpg" caption:@"Golden Gate Bridge. Golden Gate Bridge. Golden Gate Bridge. Golden Gate Bridge. Golden Gate Bridge. Golden Gate Bridge. Golden Gate Bridge. Golden Gate Bridge" author:[StoriaProfile randomizeStoriaProfile]];
    return image;
}

@end
