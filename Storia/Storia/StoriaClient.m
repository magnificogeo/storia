//
//  StoriaClient.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaClient.h"
#import "AFJSONRequestOperation.h"
#import "AFHTTPClient.h"
#import "PDKeychainBindings.h"

@implementation StoriaClient

+ (void) makeJSONRequestWithURLPath: (NSString*) urlPath
                             method: (NSString*) method
                         parameters: (NSDictionary *) parameters
                           withAuth: (BOOL) withAuth
                            success:(void (^)(NSURLRequest *request, NSHTTPURLResponse *response, id JSON))success
                            failure:(void (^)(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error, id JSON))failure {
    
    AFHTTPClient *httpClient = [[AFHTTPClient alloc] initWithBaseURL:[NSURL URLWithString:BASE_URL]];
    
    if (withAuth == YES){
        
        NSString *userID = [[PDKeychainBindings sharedKeychainBindings] stringForKey:USER_ID_KEY];
        NSString *apiToken = [[PDKeychainBindings sharedKeychainBindings] stringForKey:API_TOKEN_KEY];
        
        [httpClient setAuthorizationHeaderWithUsername:userID password:apiToken];
    }
    
    [httpClient setParameterEncoding:AFFormURLParameterEncoding];
    [httpClient registerHTTPOperationClass:[AFJSONRequestOperation class]];
    
    NSMutableURLRequest *request = [httpClient requestWithMethod:method
                                                            path:urlPath
                                                      parameters:parameters];
    
    [request setHTTPShouldHandleCookies:YES];
    
    [request setValue:@"application/x-www-form-urlencoded" forHTTPHeaderField:@"Content-Type"];
    [request setValue: [NSString stringWithFormat:@"json"] forHTTPHeaderField:@"Mime-Type"];
    
    DLog(@"header: %@", [request allHTTPHeaderFields]);
    
    AFJSONRequestOperation *operation  = [AFJSONRequestOperation JSONRequestOperationWithRequest:(NSURLRequest *)request success:^(NSURLRequest *request, NSHTTPURLResponse *response, id JSON) {
        success(request, response, JSON);
    } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error, id JSON){
        failure(request, response, error, JSON);
    }];
    
    [operation start];
}

+ (void)loginWithDict:(NSDictionary *)dict success:(void (^)(NSURLRequest *, NSHTTPURLResponse *, id))success failure:(void (^)(NSURLRequest *, NSHTTPURLResponse *, NSError *, id))failure {
    [StoriaClient makeJSONRequestWithURLPath:@"/api/login" method:@"POST" parameters:dict withAuth:NO success:success failure:failure];
}

+ (void)getFeedsWithSuccess:(void (^)(NSURLRequest *, NSHTTPURLResponse *, id))success failure:(void (^)(NSURLRequest *, NSHTTPURLResponse *, NSError *, id))failure {
    [StoriaClient makeJSONRequestWithURLPath:@"/api/feeds" method:@"GET" parameters:nil withAuth:NO success:success failure:failure];
}

+ (void)getUserInformationWithSuccess:(void (^)(NSURLRequest *, NSHTTPURLResponse *, id))success failure:(void (^)(NSURLRequest *, NSHTTPURLResponse *, NSError *, id))failure {
    [StoriaClient makeJSONRequestWithURLPath:@"/api/profile" method:@"GET" parameters:nil withAuth:NO success:success failure:failure];
}

+ (void)getUserPersonalStoryStreamWithSuccess:(void (^)(NSURLRequest *, NSHTTPURLResponse *, id))success failure:(void (^)(NSURLRequest *, NSHTTPURLResponse *, NSError *, id))failure {
    
}

+ (void)submitStoryWithParams:(NSDictionary *)dict WithSuccess:(void (^)(NSURLRequest *, NSHTTPURLResponse *, id))success failure:(void (^)(NSURLRequest *, NSHTTPURLResponse *, NSError *, id))failure {
    [StoriaClient makeJSONRequestWithURLPath:@"/api/posts" method:@"POST" parameters:dict withAuth:NO success:success failure:failure];
}

+ (BOOL)isUserLoggedIn {
    return YES;
}

@end
