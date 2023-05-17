import { MaterialIcons } from '@expo/vector-icons';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import CachedImage from 'expo-cached-image';
import { Box, Button, Icon, Pressable, ScrollView, Container, HStack, Text, Badge, Center } from 'native-base';
import React from 'react';
import _ from 'lodash';
import { useQueryClient, useQuery } from '@tanstack/react-query';

// custom components and helper files
import { loadingSpinner } from '../../components/loadingSpinner';
import { formatDiscoveryVersion, getPickupLocations, reloadBrowseCategories } from '../../util/loadLibrary';
import { getBrowseCategoryListForUser, getILSMessages, updateBrowseCategoryStatus } from '../../util/loadPatron';
import DisplayBrowseCategory from './Category';
import { BrowseCategoryContext, CheckoutsContext, HoldsContext, LanguageContext, LibrarySystemContext, UserContext } from '../../context/initialContext';
import { getLists } from '../../util/api/list';
import { navigateStack } from '../../helpers/RootNavigator';
import { getLinkedAccounts, getPatronCheckedOutItems, getPatronHolds } from '../../util/api/user';
import { getTermFromDictionary } from '../../translations/TranslationService';

let maxCategories = 5;

export const DiscoverHomeScreen = () => {
     const queryClient = useQueryClient();
     const [loading, setLoading] = React.useState(false);
     const { user, locations, accounts, cards, lists, updatePickupLocations, updateLinkedAccounts, updateLists, updateLibraryCards } = React.useContext(UserContext);
     const { library } = React.useContext(LibrarySystemContext);
     const { category, updateBrowseCategories, updateBrowseCategoryList, updateMaxCategories } = React.useContext(BrowseCategoryContext);
     const { checkouts, updateCheckouts } = React.useContext(CheckoutsContext);
     const { holds, updateHolds, pendingSortMethod, readySortMethod } = React.useContext(HoldsContext);
     const { language } = React.useContext(LanguageContext);
     const version = formatDiscoveryVersion(library.discoveryVersion);

     const [unlimited, setUnlimitedCategories] = React.useState(false);

     useQuery(['browse_categories', library.baseUrl], () => reloadBrowseCategories(maxCategories, library.baseUrl), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          onSuccess: (data) => {
               if (maxCategories === 9999) {
                    setUnlimitedCategories(true);
               }
               updateBrowseCategories(data);
               setLoading(false);
          },
     });

     useQuery(['holds', library.baseUrl, language], () => getPatronHolds(readySortMethod, pendingSortMethod, 'all', library.baseUrl, true, language), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => updateHolds(data),
     });

     useQuery(['checkouts', library.baseUrl, language], () => getPatronCheckedOutItems('all', library.baseUrl, true, language), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => updateCheckouts(data),
     });

     useQuery(['lists', library.baseUrl, language], () => getLists(library.baseUrl), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => updateLists(data),
     });

     useQuery(['linked_accounts', library.baseUrl, language], () => getLinkedAccounts(user, cards, library), {
          refetchInterval: 60 * 1000 * 15,
          refetchIntervalInBackground: true,
          notifyOnChangeProps: ['data'],
          onSuccess: (data) => {
               updateLinkedAccounts(data.accounts);
               updateLibraryCards(data.cards);
          },
     });

     useQuery(['ils_messages', library.baseUrl, language], () => getILSMessages(library.baseUrl), {
          refetchInterval: 60 * 1000 * 5,
          refetchIntervalInBackground: true,
     });

     const renderHeader = (title, key, user, url) => {
          return (
               <Box>
                    <HStack space={3} alignItems="center" justifyContent="space-between" pb={2}>
                         <Text
                              maxWidth="80%"
                              bold
                              mb={1}
                              fontSize={{
                                   base: 'lg',
                                   lg: '2xl',
                              }}>
                              {title}
                         </Text>
                         <Button size="xs" colorScheme="trueGray" variant="ghost" onPress={() => onHideCategory(url, key)} startIcon={<Icon as={MaterialIcons} name="close" size="xs" mr={-1.5} />}>
                              {getTermFromDictionary(language, 'hide')}
                         </Button>
                    </HStack>
               </Box>
          );
     };

     const renderRecord = (data, url, version, index) => {
          const item = data.item;
          let type = 'grouped_work';
          if (!_.isUndefined(item.source)) {
               type = item.source;
          }

          const imageUrl = library.baseUrl + '/bookcover.php?id=' + item.id + '&size=medium&type=' + type.toLowerCase();

          let isNew = false;
          if (typeof item.isNew !== 'undefined') {
               isNew = item.isNew;
          }

          return (
               <Pressable
                    ml={1}
                    mr={3}
                    onPress={() => onPressItem(item.id, type, item.title_display, version)}
                    width={{
                         base: 100,
                         lg: 200,
                    }}
                    height={{
                         base: 125,
                         lg: 250,
                    }}>
                    {version >= '22.08.00' && isNew ? (
                         <Container zIndex={1}>
                              <Badge colorScheme="warning" shadow={1} mb={-2} ml={-1} _text={{ fontSize: 9 }}>
                                   {getTermFromDictionary(language, 'flag_new')}
                              </Badge>
                         </Container>
                    ) : null}
                    <CachedImage
                         cacheKey={item.id}
                         alt={item.title_display}
                         source={{
                              uri: `${imageUrl}`,
                              expiresIn: 86400,
                         }}
                         style={{
                              width: '100%',
                              height: '100%',
                              borderRadius: 4,
                         }}
                    />
               </Pressable>
          );
     };

     const onPressItem = (key, type, title, version) => {
          if (version >= '22.07.00') {
               if (type === 'List') {
                    navigateStack('SearchTab', 'SearchByList', {
                         id: key,
                         url: library.baseUrl,
                         title: title,
                         userContext: user,
                         libraryContext: library,
                    });
               } else if (type === 'SavedSearch') {
                    navigateStack('SearchTab', 'SearchBySavedSearch', {
                         id: key,
                         url: library.baseUrl,
                         title: title,
                         userContext: user,
                         libraryContext: library,
                    });
               } else {
                    if (version >= '23.01.00') {
                         navigateStack('HomeTab', 'GroupedWorkScreen', {
                              id: key,
                              title: title,
                              prevRoute: 'DiscoveryScreen',
                         });
                    } else {
                         navigateStack('HomeTab', 'GroupedWorkScreen221200', {
                              id: key,
                              title: title,
                              url: library.baseUrl,
                              userContext: user,
                              libraryContext: library,
                         });
                    }
               }
          } else {
               navigateStack('HomeTab', 'GroupedWorkScreen', {
                    id: key,
                    url: library.baseUrl,
                    title: title,
                    userContext: user,
                    libraryContext: library,
                    prevRoute: 'DiscoveryScreen',
               });
          }
     };

     const renderLoadMore = () => {};

     const onHideCategory = async (url, category) => {
          setLoading(true);
          await updateBrowseCategoryStatus(category).then(async (response) => {
               await onRefreshCategories();
               await getBrowseCategoryListForUser().then((result) => {
                    updateBrowseCategoryList(result);
                    queryClient.setQueryData(['browse_categories_list', library.baseUrl], result);
                    setLoading(false);
               });
          });
     };

     const onRefreshCategories = async () => {
          setLoading(true);
          await reloadBrowseCategories(maxCategories, library.baseUrl).then((result) => {
               queryClient.setQueryData(['browse_categories', library.baseUrl], result);
               updateBrowseCategories(result);
               setLoading(false);
          });
     };

     const onLoadAllCategories = () => {
          maxCategories = 9999;
          updateMaxCategories(9999);
          setUnlimitedCategories(true);
          onRefreshCategories();
     };

     const onPressSettings = (url, patronId) => {
          const version = formatDiscoveryVersion(library.discoveryVersion);
          let screen = 'SettingsHomeScreen';
          if (version >= '22.12.00') {
               screen = 'SettingsBrowseCategories';
          }
          navigateStack('AccountScreenTab', screen, {
               url,
               patronId,
          });
     };

     const handleOnPressCategory = (label, key, source) => {
          let screen = 'SearchByCategory';
          if (source === 'List') {
               screen = 'SearchByList';
          } else if (source === 'SavedSearch') {
               screen = 'SearchBySavedSearch';
          }

          navigateStack('SearchTab', screen, {
               title: label,
               id: key,
               url: library.baseUrl,
               libraryContext: library,
               userContext: user,
               language: language,
          });
     };

     if (loading === true) {
          return loadingSpinner();
     }

     return (
          <ScrollView>
               <Box safeArea={5}>
                    {category.map((item, index) => {
                         return <DisplayBrowseCategory language={language} key={index} categoryLabel={item.title} categoryKey={item.key} id={item.id} records={item.records} isHidden={item.isHidden} categorySource={item.source} renderRecords={renderRecord} header={renderHeader} hideCategory={onHideCategory} user={user} libraryUrl={library.baseUrl} loadMore={renderLoadMore} discoveryVersion={library.version} onPressCategory={handleOnPressCategory} categoryList={category} />;
                    })}
                    <ButtonOptions language={language} libraryUrl={library.baseUrl} patronId={user.id} onPressSettings={onPressSettings} onRefreshCategories={onRefreshCategories} discoveryVersion={library.discoveryVersion} loadAll={unlimited} onLoadAllCategories={onLoadAllCategories} />
               </Box>
          </ScrollView>
     );
};

const ButtonOptions = (props) => {
     const [loading, setLoading] = React.useState(false);
     const [refreshing, setRefreshing] = React.useState(false);
     const { language, onPressSettings, onRefreshCategories, libraryUrl, patronId, discoveryVersion, loadAll, onLoadAllCategories } = props;

     const version = formatDiscoveryVersion(discoveryVersion);

     if (version >= '22.07.00') {
          return (
               <Box>
                    {!loadAll ? (
                         <Button
                              isLoading={loading}
                              size="md"
                              colorScheme="primary"
                              onPress={() => {
                                   setLoading(true);
                                   onLoadAllCategories(libraryUrl, patronId);
                                   setTimeout(function () {
                                        setLoading(false);
                                   }, 5000);
                              }}
                              startIcon={<Icon as={MaterialIcons} name="schedule" size="sm" />}>
                              {getTermFromDictionary(language, 'browse_categories_load_all')}
                         </Button>
                    ) : null}
                    <Button
                         size="md"
                         mt="3"
                         colorScheme="primary"
                         onPress={() => {
                              onPressSettings(libraryUrl, patronId);
                         }}
                         startIcon={<Icon as={MaterialIcons} name="settings" size="sm" />}>
                         {getTermFromDictionary(language, 'browse_categories_manage')}
                    </Button>
                    <Button
                         isLoading={refreshing}
                         size="md"
                         mt="3"
                         colorScheme="primary"
                         onPress={() => {
                              setRefreshing(true);
                              onRefreshCategories();
                              setTimeout(function () {
                                   setRefreshing(false);
                              });
                         }}
                         startIcon={<Icon as={MaterialIcons} name="refresh" size="sm" />}>
                         {getTermFromDictionary(language, 'browse_categories_refresh')}
                    </Button>
               </Box>
          );
     }

     return (
          <Box>
               <Button
                    size="md"
                    colorScheme="primary"
                    onPress={() => {
                         onPressSettings(libraryUrl, patronId);
                    }}
                    startIcon={<Icon as={MaterialIcons} name="settings" size="sm" />}>
                    {getTermFromDictionary(language, 'browse_categories_manage')}
               </Button>
               <Button
                    size="md"
                    mt="3"
                    colorScheme="primary"
                    onPress={() => {
                         onRefreshCategories(libraryUrl);
                    }}
                    startIcon={<Icon as={MaterialIcons} name="refresh" size="sm" />}>
                    {getTermFromDictionary(language, 'browse_categories_refresh')}
               </Button>
          </Box>
     );
};