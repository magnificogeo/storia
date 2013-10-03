//
//  StoriaImage.h
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import <Foundation/Foundation.h>
#import "StoriaProfile.h"

@interface StoriaImage : NSObject

@property (nonatomic, strong) NSString *imageUrl;
@property (nonatomic, strong) NSString *caption;
@property (nonatomic, strong) StoriaProfile *author;

- (id) initWithImageUrl:(NSString *)imageUrl caption:(NSString *)caption author:(StoriaProfile *)author;
+ (id) randomizeStoriaImage;

@end
