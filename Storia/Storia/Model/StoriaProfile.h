//
//  StoriaProfile.h
//  Storia
//
//  Created by Nguyen Ngoc Trung on 22/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface StoriaProfile : NSObject

@property NSString *userName;
@property NSString *realName;
@property NSString *profileImageUrl;

- (id)initWithUserName:(NSString *)name realName:(NSString *)realName profileImageUrl:(NSString *)profileImageUrl;

+ (id)randomizeStoriaProfile;

@end
