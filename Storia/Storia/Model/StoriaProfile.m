//
//  StoriaProfile.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 22/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaProfile.h"

@implementation StoriaProfile

- (id)initWithUserName:(NSString *)name realName:(NSString *)realName profileImageUrl:(NSString *)profileImageUrl {
    if (self = [super init]) {
        self.userName = name;
        self.realName = realName;
        self.profileImageUrl = profileImageUrl;
    }
    return self;
}

+ (id)randomizeStoriaProfile {
    StoriaProfile *profile = [[StoriaProfile alloc] init];
    profile.userName = @"trunga0";
    profile.realName = @"Nguyen Ngoc Trung";
    profile.profileImageUrl = @"https://fbcdn-profile-a.akamaihd.net/hprofile-ak-prn1/c30.20.131.131/s100x100/993986_10200672408827789_943135983_a.jpg";
    return profile;
}

@end
