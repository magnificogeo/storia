//
//  StoriaAuthViewController.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaAuthViewController.h"
#import "StoriaClient.h"
#import "StoriaTabBarController.h"

@interface StoriaAuthViewController ()

@property (weak, nonatomic) IBOutlet UITextField *usernameTextField;
@property (weak, nonatomic) IBOutlet UITextField *passwordTextField;
@end

@implementation StoriaAuthViewController

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Custom initialization
    }
    return self;
}

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view.
}

- (void)viewDidAppear:(BOOL)animated {
    [super viewDidAppear:animated];
    if ([[NSUserDefaults standardUserDefaults] valueForKey:@"LOGGED_IN?"] != nil) {
        StoriaTabBarController *storiaTabBarController =[self.storyboard instantiateViewControllerWithIdentifier:@"storiaTabBar"];
        [storiaTabBarController setModalTransitionStyle:UIModalTransitionStyleCrossDissolve];
        [self presentViewController:storiaTabBarController animated:YES completion:nil];
    }
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (IBAction)loginPressed:(id)sender {
    NSDictionary *params = @{@"user_name": self.usernameTextField.text,
                             @"password": self.passwordTextField.text};
    [StoriaClient loginWithDict:params success:^(NSURLRequest *request, NSHTTPURLResponse *response, id JSON) {
        DLog(@"success");
        [[NSUserDefaults standardUserDefaults] setValue:@"OK" forKey:@"LOGGED_IN?"];
        StoriaTabBarController *storiaTabBarController =[self.storyboard instantiateViewControllerWithIdentifier:@"storiaTabBar"];
        [storiaTabBarController setModalTransitionStyle:UIModalTransitionStyleCrossDissolve];
        [self presentViewController:storiaTabBarController animated:YES completion:nil];
    } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error, id JSON) {
        DLog(@"failure");
    }];
}

@end
