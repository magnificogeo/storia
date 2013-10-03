//
//  StoriaFeedsViewController.m
//  Storia
//
//  Created by Nguyen Ngoc Trung on 21/9/13.
//  Copyright (c) 2013 Jubbs. All rights reserved.
//

#import "StoriaFeedsViewController.h"
#import "StoriaFeedsTableViewCell.h"
#import "StoriaStoryViewController.h"
#import "StoriaClient.h"

@interface StoriaFeedsViewController () <UITableViewDataSource, UITableViewDelegate>

@property (nonatomic, strong) UITableView *tableView;
@property (nonatomic, strong) NSMutableArray *feedItemsArray;
@property (nonatomic, assign) int selectedRow;

@end

@implementation StoriaFeedsViewController

# pragma mark - View Lifecycle

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
    self.tableView = [[UITableView alloc] initWithFrame:self.view.frame];
    self.tableView.dataSource = self;
    self.tableView.delegate = self;
    self.tableView.separatorColor = [UIColor clearColor];
    [self.view addSubview:self.tableView];

//    self.feedItemsArray = [NSMutableArray arrayWithArray:@[[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory],[StoriaStory randomizeStoriaStory]]];
    self.feedItemsArray = [NSMutableArray array];
    [StoriaClient getFeedsWithSuccess:^(NSURLRequest *request, NSHTTPURLResponse *response, id JSON) {
        DLog(@"%@", JSON);
        NSArray *feedItems = JSON[@"feeds"];
        for (NSDictionary *feedItem in feedItems) {
            StoriaStory *story = [[StoriaStory alloc] initWithStoryName:feedItem[@"title"] description:feedItem[@"description"] backgroundImageUrl:feedItem[@"main_image_url"] ImageUrlsArray:feedItem[@"images"]];
            [self.feedItemsArray addObject:story];
        }
        [self.tableView reloadData];
    } failure:^(NSURLRequest *request, NSHTTPURLResponse *response, NSError *error, id JSON) {
        DLog(@"Failure");
    }];
}

- (void)viewDidAppear:(BOOL)animated {
    [super viewDidAppear:animated];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

# pragma mark - TableView datasource and delegate

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
//    return nil;
    if (indexPath.row == 0 || indexPath.row == self.feedItemsArray.count + 1) {
        UITableViewCell *cell = [[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:@"blankCell"];
        cell.backgroundColor = [UIColor colorWithWhite:0.9 alpha:1.0];
        return cell;
    } else {
        static NSString *identifier = @"StoriaFeedsCell";
        StoriaFeedsTableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:identifier];
        if (!cell) {
            cell = [[StoriaFeedsTableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:identifier];
        }
        [cell reloadViewsWithStory:[self.feedItemsArray objectAtIndex:indexPath.row - 1]];
        return cell;
    }
}

- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath {
    if (indexPath.row == 0) {
        CGFloat navBarHeight = self.navigationController.navigationBar.frame.size.height;
        CGFloat statusBarHeight = [[UIApplication sharedApplication] statusBarFrame].size.height;
        return navBarHeight + statusBarHeight;
    } else if (indexPath.row == self.feedItemsArray.count + 1) {
        return self.tabBarController.tabBar.frame.size.height;
    } else {
        return 300;
    }
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    return self.feedItemsArray.count + 2;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
    self.selectedRow = indexPath.row;
    [self performSegueWithIdentifier:@"toStoryViewController" sender:self];
}

# pragma mark - Segue

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    StoriaStoryViewController *viewController = (StoriaStoryViewController *)segue.destinationViewController;
    viewController.story = [self.feedItemsArray objectAtIndex:self.selectedRow - 1];
}

@end
