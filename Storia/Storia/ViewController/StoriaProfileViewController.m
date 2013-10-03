//
//  StoriaProfileViewController.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaProfileViewController.h"
#import "StoriaStory.h"
#import "StoriaProfile.h"
#import "StoriaFeedsTableViewCell.h"
#import "StoriaProfileTableViewCell.h"
#import "StoriaStoryViewController.h"
#import "StoriaClient.h"

@interface StoriaProfileViewController () <UITableViewDataSource, UITableViewDelegate>

@property (nonatomic, strong) UITableView *mainTableView;
@property (nonatomic, strong) StoriaProfile *profile;
@property (nonatomic, strong) NSMutableArray *personalStories;
@property (nonatomic, assign) int selectedIndex;

@end

@implementation StoriaProfileViewController

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
    self.mainTableView = [[UITableView alloc] initWithFrame:self.view.frame];
    self.mainTableView.dataSource = self;
    self.mainTableView.delegate = self;
    self.mainTableView.separatorColor = [UIColor clearColor];
    [self.view addSubview:self.mainTableView];
    
//    self.personalStories = [NSMutableArray arrayWithArray:@[[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory]]];
    self.personalStories = [NSMutableArray array];
    [StoriaClient getUserInformationWithSuccess:^(NSURLRequest *request, NSHTTPURLResponse *response, id JSON) {
        DLog(@"%@", JSON);
        self.profile = [[StoriaProfile alloc] initWithUserName:JSON[@"user_name"] realName:JSON[@"real_name"] profileImageUrl:JSON[@"profile_picture_url"]];
        for (NSDictionary *feedItem in JSON[@"stories"]) {
            StoriaStory *story = [[StoriaStory alloc] initWithStoryName:feedItem[@"title"] description:feedItem[@"description"] backgroundImageUrl:feedItem[@"main_image_url"] ImageUrlsArray:feedItem[@"images"]];
            [self.personalStories addObject:story];
        }
        [self.mainTableView reloadData];
    } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error, id JSON) {
        DLog(@"Failure");
    }];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

# pragma mark - UITableView datasource and delegate
- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    if (indexPath.row == 0 || indexPath.row == self.personalStories.count + 2) {
        UITableViewCell *cell = [[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:@"blankCell"];
        cell.backgroundColor = [UIColor colorWithWhite:0.9 alpha:1.0];
        return cell;
    } else if (indexPath.row == 1) {
        StoriaProfileTableViewCell *cell = [[StoriaProfileTableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:@"profileCell"];
        if (self.profile)
            [cell updateWithProfile:self.profile];
        return cell;
    } else {
        static NSString *identifier = @"StoriaFeedsCell";
        StoriaFeedsTableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:identifier];
        if (!cell) {
            cell = [[StoriaFeedsTableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:identifier];
        }
        [cell reloadViewsWithStory:[self.personalStories objectAtIndex:indexPath.row - 2]];
        return cell;
    }
}

- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath {
    if (indexPath.row == 0) {
        CGFloat navBarHeight = self.navigationController.navigationBar.frame.size.height;
        CGFloat statusBarHeight = [[UIApplication sharedApplication] statusBarFrame].size.height;
        return navBarHeight + statusBarHeight;
    } else if (indexPath.row == self.personalStories.count + 2) {
        return self.tabBarController.tabBar.frame.size.height;
    } else if (indexPath.row == 1) {
        return 120;
    } else {
        return 300;
    }
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
    if (indexPath.row > 1 && indexPath.row < self.personalStories.count + 2) {
        self.selectedIndex = indexPath.row;
        [self performSegueWithIdentifier:@"profileToStory" sender:self];
    }
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    return self.personalStories.count + 3;
}

# pragma mark - Segue

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    if ([segue.identifier isEqualToString:@"profileToStory"]) {
        StoriaStoryViewController *viewController = (StoriaStoryViewController *)segue.destinationViewController;
        viewController.story = [self.personalStories objectAtIndex:self.selectedIndex - 2];
    }
}

- (IBAction)createStoryButtonClicked:(id)sender {
    DLog(@"Clicked");
}

@end
