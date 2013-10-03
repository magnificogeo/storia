//
//  StoriaStory.h
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import <Foundation/Foundation.h>
#import "StoriaImage.h"
#import "StoriaProfile.h"

@interface StoriaStory : NSObject

@property (nonatomic, strong) NSString *name;
@property (nonatomic, strong) NSString *description;
@property (nonatomic, strong) NSString *backgroundImageUrl;
@property (nonatomic, strong) NSArray *imagesArray;
@property (nonatomic, strong) StoriaProfile *author;

- (id)initWithStoryName:(NSString *)name
            description:(NSString *)description
     backgroundImageUrl:(NSString *)backgroundImageUrl
         ImageUrlsArray:(NSArray *)imageArray;

+ (id)randomizeStoriaStory;

@end
