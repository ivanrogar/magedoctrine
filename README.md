Doctrine for Magento 2

- the default database connection will be used
- add entity classes to your module in a folder like this: YourModule\Entity
- make your module known via DI like this:

```xml
    <type name="JohnRogar\MageDoctrine\Api\Data\Doctrine">
            <arguments>
                <argument name="modules" xsi:type="array">
                    <item name="yourvendor_yourmodule" xsi:type="string">
                        YourVendor_YourModule
                    </item>
                </argument>
            </arguments>
        </type>
```

- you can also tweak the configuration through DI 
- run schema commands through Magento console to create, update or drop the schema
- a special schema tool is used when doing updates so that it ignores all other tables except the ones which are in the Doctrine schema
