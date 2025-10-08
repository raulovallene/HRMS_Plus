import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AppeasementMgmt } from './appeasement-mgmt';

describe('AppeasementMgmt', () => {
  let component: AppeasementMgmt;
  let fixture: ComponentFixture<AppeasementMgmt>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AppeasementMgmt]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AppeasementMgmt);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
